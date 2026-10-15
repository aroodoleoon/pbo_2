package com.pos.server.service

import com.pos.server.domain.enums.PaymentMethod
import com.pos.server.domain.enums.PaymentStatus
import com.pos.server.domain.exception.ValidasiException
import com.pos.server.dto.sales.CashPaymentLegacyRequest
import com.pos.server.dto.sales.PaymentResponse
import com.pos.server.dto.sales.ProcessPaymentRequest
import com.pos.server.dto.sales.SaleItemRequest
import com.pos.server.repository.TransaksiRepository
import java.time.Instant
import java.util.UUID

/**
 * Layanan Pembayaran POS (Modul 5 Josua).
 * Menjamin eksekusi atomik antara simpan transaksi, detail, pembayaran, dan pemotongan stok.
 */
class PaymentService(
    private val transaksiRepository: TransaksiRepository,
    private val stockGateway: StockGateway
) {
    /**
     * Memproses pembayaran tunai sesuai implementasi awal Josua (Modul 5).
     */
    fun payCashLegacy(request: CashPaymentLegacyRequest): PaymentResponse {
        val sale = request.sale
        require(sale.transactionId.isNotBlank()) { "ID transaksi wajib diisi." }
        require(sale.cashierId.isNotBlank()) { "ID kasir wajib diisi." }
        require(sale.items.isNotEmpty()) { "Transaksi harus memiliki minimal satu barang." }
        require(sale.items.all { it.itemId.isNotBlank() && it.quantity > 0 }) {
            "Kode barang wajib diisi dan jumlah harus lebih dari nol."
        }
        require(sale.items.map { it.itemId }.distinct().size == sale.items.size) {
            "Barang duplikat; gabungkan jumlah item yang sama terlebih dahulu."
        }
        require(sale.discountAmount >= 0) { "Diskon tidak boleh negatif." }
        require(request.amountPaid >= 0) { "Nominal bayar tidak boleh negatif." }

        val paymentId = "PAY-${UUID.randomUUID()}"
        val transactionNumber = "TRX-${System.currentTimeMillis()}"

        return transaksiRepository.inTransaction { c ->
            val saleItems = sale.items.map { SaleItemRequest(it.itemId, it.quantity) }
            val lines = stockGateway.prepareSale(c, saleItems)
            require(lines.isNotEmpty()) { "Detail transaksi tidak ditemukan." }

            val subtotal = lines.fold(0L) { sum, line -> Math.addExact(sum, line.subtotal) }
            require(sale.discountAmount <= subtotal) { "Diskon melebihi subtotal." }
            val total = Math.subtractExact(subtotal, sale.discountAmount)
            require(request.amountPaid >= total) { "Pembayaran tunai kurang dari total belanja." }
            val change = Math.subtractExact(request.amountPaid, total)

            // Simpan header transaksi
            transaksiRepository.insertSaleHeader(
                c = c,
                transactionId = sale.transactionId,
                transactionNumber = transactionNumber,
                cashierId = sale.cashierId,
                customerId = sale.customerId,
                subtotal = subtotal,
                discount = sale.discountAmount,
                tax = 0L,
                total = total,
                discountDescription = if (sale.discountAmount > 0) "Potongan diskon belanja" else null
            )

            // Simpan detail transaksi
            lines.forEachIndexed { index, line ->
                transaksiRepository.insertSaleDetail(
                    c = c,
                    detailId = "${sale.transactionId}-D${index + 1}",
                    transactionId = sale.transactionId,
                    productId = line.itemId,
                    quantity = line.quantity,
                    priceAtTransaction = line.unitPrice,
                    subtotal = line.subtotal
                )
            }

            // Simpan pembayaran
            transaksiRepository.insertPayment(
                c = c,
                paymentId = paymentId,
                transactionId = sale.transactionId,
                method = PaymentMethod.CASH,
                amountPaid = request.amountPaid,
                change = change,
                status = PaymentStatus.SUCCESS
            )

            // Potong stok dan catat pergerakan stok atomik via Modul 2 Alex
            stockGateway.applySale(c, sale.transactionId, sale.cashierId, lines)

            PaymentResponse(
                paymentId = paymentId,
                transactionId = sale.transactionId,
                method = "CASH",
                subtotal = subtotal,
                discountAmount = sale.discountAmount,
                total = total,
                amountPaid = request.amountPaid,
                changeAmount = change,
                status = "SUCCESS",
                timestamp = Instant.now().toString()
            )
        }
    }

    /**
     * Memproses pembayaran multi-metode (CASH, QRIS, TRANSFER, DEBIT) sesuai kontrak API POS.
     */
    fun processPayment(
        transactionId: String,
        cashierId: String,
        customerId: String?,
        items: List<SaleItemRequest>,
        discountAmount: Long,
        taxAmount: Long,
        request: ProcessPaymentRequest
    ): PaymentResponse {
        val method = PaymentMethod.fromString(request.method)
        val paymentId = "PAY-${UUID.randomUUID()}"
        val transactionNumber = "TRX-${System.currentTimeMillis()}"

        return transaksiRepository.inTransaction { c ->
            val lines = stockGateway.prepareSale(c, items)
            val subtotal = lines.fold(0L) { sum, line -> Math.addExact(sum, line.subtotal) }
            val netTotal = subtotal - discountAmount + taxAmount

            val (amountPaid, change) = when (method) {
                PaymentMethod.CASH -> {
                    if (request.amountPaid < netTotal) {
                        throw ValidasiException("Pembayaran tunai kurang dari total belanja (kurang: ${netTotal - request.amountPaid}).")
                    }
                    Pair(request.amountPaid, request.amountPaid - netTotal)
                }
                PaymentMethod.QRIS, PaymentMethod.TRANSFER, PaymentMethod.DEBIT -> {
                    Pair(netTotal, 0L)
                }
            }

            transaksiRepository.insertSaleHeader(
                c = c,
                transactionId = transactionId,
                transactionNumber = transactionNumber,
                cashierId = cashierId,
                customerId = customerId,
                subtotal = subtotal,
                discount = discountAmount,
                tax = taxAmount,
                total = netTotal,
                discountDescription = if (discountAmount > 0) "Potongan diskon belanja" else null
            )

            lines.forEachIndexed { index, line ->
                transaksiRepository.insertSaleDetail(
                    c = c,
                    detailId = "$transactionId-D${index + 1}",
                    transactionId = transactionId,
                    productId = line.itemId,
                    quantity = line.quantity,
                    priceAtTransaction = line.unitPrice,
                    subtotal = line.subtotal
                )
            }

            transaksiRepository.insertPayment(
                c = c,
                paymentId = paymentId,
                transactionId = transactionId,
                method = method,
                amountPaid = amountPaid,
                change = change,
                status = PaymentStatus.SUCCESS
            )

            stockGateway.applySale(c, transactionId, cashierId, lines)

            PaymentResponse(
                paymentId = paymentId,
                transactionId = transactionId,
                method = method.name,
                subtotal = subtotal,
                discountAmount = discountAmount,
                total = netTotal,
                amountPaid = amountPaid,
                changeAmount = change,
                status = "SUCCESS",
                timestamp = Instant.now().toString()
            )
        }
    }

    private val idempotencyCache = java.util.concurrent.ConcurrentHashMap<String, com.pos.server.dto.sales.CheckoutResponse>()

    fun checkout(cashierId: String, request: com.pos.server.dto.sales.CheckoutRequest): com.pos.server.dto.sales.CheckoutResponse {
        val key = request.idempotencyKey?.trim()
        if (!key.isNullOrBlank()) {
            idempotencyCache[key]?.let { return it }
        }

        require(request.items.isNotEmpty()) { "Keranjang belanja tidak boleh kosong." }
        require(request.items.all { it.productId.isNotBlank() && it.quantity > 0 }) {
            "ID produk harus diisi dan kuantitas harus lebih dari nol."
        }
        require(request.payment.amountPaid >= 0) { "Nominal bayar tidak boleh negatif." }

        val method = PaymentMethod.fromString(request.payment.method)
        val transactionId = UUID.randomUUID().toString()
        val transactionNumber = "TRX-${System.currentTimeMillis()}"
        val paymentId = "PAY-${UUID.randomUUID()}"

        val response = transaksiRepository.inTransaction { c ->
            val saleItems = request.items.map { SaleItemRequest(it.productId, it.quantity) }
            val lines = stockGateway.prepareSale(c, saleItems)
            require(lines.isNotEmpty()) { "Detail transaksi tidak ditemukan." }

            val subtotal = lines.fold(0L) { sum, line -> Math.addExact(sum, line.subtotal) }
            val discount = 0L
            val tax = 0L
            val total = subtotal - discount + tax

            val (amountPaid, change) = when (method) {
                PaymentMethod.CASH -> {
                    if (request.payment.amountPaid < total) {
                        throw ValidasiException("Pembayaran tunai kurang dari total belanja (Total: Rp$total, Dibayar: Rp${request.payment.amountPaid}).")
                    }
                    Pair(request.payment.amountPaid, request.payment.amountPaid - total)
                }
                PaymentMethod.QRIS, PaymentMethod.TRANSFER, PaymentMethod.DEBIT -> {
                    Pair(total, 0L)
                }
            }

            // Simpan header transaksi
            transaksiRepository.insertSaleHeader(
                c = c,
                transactionId = transactionId,
                transactionNumber = transactionNumber,
                cashierId = cashierId,
                customerId = request.customerId,
                subtotal = subtotal,
                discount = discount,
                tax = tax,
                total = total,
                discountDescription = null
            )

            // Simpan detail transaksi
            lines.forEachIndexed { index, line ->
                transaksiRepository.insertSaleDetail(
                    c = c,
                    detailId = "$transactionId-D${index + 1}",
                    transactionId = transactionId,
                    productId = line.itemId,
                    quantity = line.quantity,
                    priceAtTransaction = line.unitPrice,
                    subtotal = line.subtotal
                )
            }

            // Simpan pembayaran
            transaksiRepository.insertPayment(
                c = c,
                paymentId = paymentId,
                transactionId = transactionId,
                method = method,
                amountPaid = amountPaid,
                change = change,
                status = PaymentStatus.SUCCESS
            )

            // Kurangi stok dan catat pergerakan stok atomik via StockGateway
            stockGateway.applySale(c, transactionId, cashierId, lines)

            val itemResponses = lines.map {
                com.pos.server.dto.sales.CheckoutItemResponseDto(
                    productId = it.itemId,
                    productName = it.itemName,
                    quantity = it.quantity,
                    price = it.unitPrice,
                    subtotal = it.subtotal
                )
            }

            com.pos.server.dto.sales.CheckoutResponse(
                transactionId = transactionId,
                transactionNumber = transactionNumber,
                status = "COMPLETED",
                subtotal = subtotal,
                discount = discount,
                tax = tax,
                total = total,
                amountPaid = amountPaid,
                change = change,
                items = itemResponses,
                payment = com.pos.server.dto.sales.CheckoutPaymentResponseDto(
                    method = method.name,
                    amountPaid = amountPaid,
                    change = change,
                    status = "SUCCESS"
                ),
                createdAt = Instant.now().toString()
            )
        }

        if (!key.isNullOrBlank()) {
            idempotencyCache[key] = response
        }
        return response
    }
}

