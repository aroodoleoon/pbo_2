package com.pos.server.payment

import com.pos.shared.payment.SalesReportRow
import com.pos.shared.payment.TransactionHistoryItem
import java.sql.Connection
import java.sql.Date
import javax.sql.DataSource

class PosRepository(private val dataSource: DataSource) {
    fun <T> inTransaction(block: (Connection) -> T): T {
        dataSource.connection.use { connection ->
            connection.autoCommit = false
            try {
                val result = block(connection)
                connection.commit()
                return result
            } catch (error: Throwable) {
                runCatching { connection.rollback() }
                throw error
            } finally {
                runCatching { connection.autoCommit = true }
            }
        }
    }

    fun insertSaleHeader(
        c: Connection, transactionId: String, cashierId: String, customerId: String?,
        subtotal: Long, discount: Long, total: Long
    ) {
        val sql = """
            INSERT INTO transaksi
            (id_transaksi, tanggal_transaksi, id_kasir, id_pelanggan, subtotal, diskon, total, status)
            VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, 'LUNAS')
        """.trimIndent()
        c.prepareStatement(sql).use {
            it.setString(1, transactionId); it.setString(2, cashierId); it.setString(3, customerId)
            it.setLong(4, subtotal); it.setLong(5, discount); it.setLong(6, total)
            it.executeUpdate()
        }
    }

    fun insertSaleDetails(c: Connection, transactionId: String, lines: List<SaleLineSnapshot>) {
        val sql = """
            INSERT INTO detail_transaksi
            (id_detail, id_transaksi, id_barang, nama_barang_snapshot, jumlah, harga_saat_transaksi, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """.trimIndent()
        c.prepareStatement(sql).use { statement ->
            lines.forEachIndexed { i, line ->
                statement.setString(1, "$transactionId-D${i + 1}")
                statement.setString(2, transactionId); statement.setString(3, line.itemId)
                statement.setString(4, line.itemName); statement.setInt(5, line.quantity)
                statement.setLong(6, line.unitPrice); statement.setLong(7, line.subtotal)
                statement.addBatch()
            }
            statement.executeBatch()
        }
    }

    fun insertCashPayment(c: Connection, paymentId: String, transactionId: String, paid: Long, change: Long) {
        val sql = """
            INSERT INTO pembayaran
            (id_pembayaran, id_transaksi, metode, nominal_bayar, kembalian, status, tanggal_pembayaran)
            VALUES (?, ?, 'TUNAI', ?, ?, 'BERHASIL', CURRENT_TIMESTAMP)
        """.trimIndent()
        c.prepareStatement(sql).use {
            it.setString(1, paymentId); it.setString(2, transactionId)
            it.setLong(3, paid); it.setLong(4, change); it.executeUpdate()
        }
    }

    fun getTransactionHistory(limit: Int = 100): List<TransactionHistoryItem> {
        require(limit in 1..1000) { "Limit harus antara 1 sampai 1000." }
        val sql = """
            SELECT t.id_transaksi, t.tanggal_transaksi, t.id_kasir, t.id_pelanggan,
                   t.subtotal, t.diskon, t.total, p.metode, p.nominal_bayar, p.kembalian, p.status
            FROM transaksi t JOIN pembayaran p ON p.id_transaksi = t.id_transaksi
            ORDER BY t.tanggal_transaksi DESC LIMIT ?
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setInt(1, limit)
                s.executeQuery().use { r ->
                    val rows = mutableListOf<TransactionHistoryItem>()
                    while (r.next()) rows += TransactionHistoryItem(
                        r.getString("id_transaksi"), r.getTimestamp("tanggal_transaksi").toInstant().toString(),
                        r.getString("id_kasir"), r.getString("id_pelanggan"), r.getLong("subtotal"),
                        r.getLong("diskon"), r.getLong("total"), r.getString("metode"),
                        r.getLong("nominal_bayar"), r.getLong("kembalian"), r.getString("status")
                    )
                    return rows
                }
            }
        }
    }

    /** Batas akhir bersifat eksklusif; route menambahkan satu hari ke tanggal 'to'. */
    fun getSalesReport(from: Date, toExclusive: Date): List<SalesReportRow> {
        require(!from.after(toExclusive)) { "Rentang tanggal tidak valid." }
        val sql = """
            SELECT CAST(t.tanggal_transaksi AS DATE) tanggal,
                   COUNT(DISTINCT t.id_transaksi) jumlah_transaksi,
                   COALESCE(SUM(t.subtotal), 0) penjualan_kotor,
                   COALESCE(SUM(t.diskon), 0) total_diskon,
                   COALESCE(SUM(t.total), 0) penjualan_bersih
            FROM transaksi t JOIN pembayaran p ON p.id_transaksi = t.id_transaksi
            WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < ? AND p.status = 'BERHASIL'
            GROUP BY CAST(t.tanggal_transaksi AS DATE)
            ORDER BY CAST(t.tanggal_transaksi AS DATE)
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setDate(1, from); s.setDate(2, toExclusive)
                s.executeQuery().use { r ->
                    val rows = mutableListOf<SalesReportRow>()
                    while (r.next()) rows += SalesReportRow(
                        r.getDate("tanggal").toString(), r.getLong("jumlah_transaksi"),
                        r.getLong("penjualan_kotor"), r.getLong("total_diskon"), r.getLong("penjualan_bersih")
                    )
                    return rows
                }
            }
        }
    }
}
