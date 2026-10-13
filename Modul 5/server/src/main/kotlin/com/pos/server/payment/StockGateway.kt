package com.pos.server.payment

import com.pos.shared.payment.SaleItemRequest
import java.sql.Connection

/**
 * Kontrak untuk inventaris Anggota 2.
 * Semua metode memakai Connection transaksi yang sama untuk atomic commit/rollback.
 */
interface StockGateway {
    /** Validasi barang aktif/stok dan ambil snapshot harga/nama; belum mengubah stok. */
    fun prepareSale(connection: Connection, items: List<SaleItemRequest>): List<SaleLineSnapshot>

    /** Implementasi inventaris mengurangi stok dan mencatat pergerakan_stok. */
    fun applySale(
        connection: Connection,
        transactionId: String,
        cashierId: String,
        items: List<SaleLineSnapshot>
    )
}

data class SaleLineSnapshot(
    val itemId: String,
    val itemName: String,
    val quantity: Int,
    val unitPrice: Long
) {
    val subtotal: Long get() = Math.multiplyExact(unitPrice, quantity)
}
