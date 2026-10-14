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
}

