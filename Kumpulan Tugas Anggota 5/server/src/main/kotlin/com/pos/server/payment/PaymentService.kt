package com.pos.server.payment

import com.pos.shared.payment.CashPaymentRequest
import com.pos.shared.payment.PaymentResult
import java.util.UUID

class PaymentService(
    private val repository: PosRepository,
    private val stockGateway: StockGateway
) {
    fun payCash(request: CashPaymentRequest): PaymentResult {
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
        return repository.inTransaction { c ->
            val lines = stockGateway.prepareSale(c, sale.items)
            require(lines.isNotEmpty()) { "Detail transaksi tidak ditemukan." }
            val subtotal = lines.fold(0L) { sum, line -> Math.addExact(sum, line.subtotal) }
            require(sale.discountAmount <= subtotal) { "Diskon melebihi subtotal." }
            val total = Math.subtractExact(subtotal, sale.discountAmount)
            require(request.amountPaid >= total) { "Pembayaran tunai kurang dari total belanja." }
            val change = Math.subtractExact(request.amountPaid, total)

            repository.insertSaleHeader(c, sale.transactionId, sale.cashierId, sale.customerId,
                subtotal, sale.discountAmount, total)
            repository.insertSaleDetails(c, sale.transactionId, lines)
            repository.insertCashPayment(c, paymentId, sale.transactionId, request.amountPaid, change)
            stockGateway.applySale(c, sale.transactionId, sale.cashierId, lines)

            PaymentResult(sale.transactionId, paymentId, subtotal, sale.discountAmount, total,
                request.amountPaid, change, "BERHASIL")
        }
    }
}
