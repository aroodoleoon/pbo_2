package com.pos.server.repository

import com.pos.server.domain.enums.PaymentMethod
import com.pos.server.domain.enums.PaymentStatus
import com.pos.server.domain.enums.TransactionStatus
import com.pos.server.dto.sales.SalesReportRowDto
import com.pos.server.dto.sales.TransactionHistoryItemDto
import java.sql.Connection
import java.sql.Date
import java.sql.Timestamp
import javax.sql.DataSource

class TransaksiRepository(private val dataSource: DataSource) {

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
        c: Connection,
        transactionId: String,
        transactionNumber: String,
        cashierId: String,
        customerId: String?,
        subtotal: Long,
        discount: Long,
        tax: Long,
        total: Long,
        discountDescription: String?,
        status: TransactionStatus = TransactionStatus.COMPLETED
    ) {
        val sql = """
            INSERT INTO transaksi
            (id, nomor_transaksi, dibuat_pada, kasir_id, pelanggan_id, subtotal, diskon, pajak, total, keterangan_syarat_diskon, status)
            VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?, ?, ?)
        """.trimIndent()
        c.prepareStatement(sql).use { s ->
            s.setString(1, transactionId)
            s.setString(2, transactionNumber)
            s.setString(3, cashierId)
            s.setString(4, customerId)
            s.setLong(5, subtotal)
            s.setLong(6, discount)
            s.setLong(7, tax)
            s.setLong(8, total)
            s.setString(9, discountDescription)
            s.setString(10, status.name)
            s.executeUpdate()
        }
    }

    fun insertSaleDetail(
        c: Connection,
        detailId: String,
        transactionId: String,
        productId: String,
        quantity: Int,
        priceAtTransaction: Long,
        subtotal: Long
    ) {
        val sql = """
            INSERT INTO detail_transaksi
            (id, transaksi_id, barang_id, jumlah, harga_saat_transaksi, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        """.trimIndent()
        c.prepareStatement(sql).use { s ->
            s.setString(1, detailId)
            s.setString(2, transactionId)
            s.setString(3, productId)
            s.setInt(4, quantity)
            s.setLong(5, priceAtTransaction)
            s.setLong(6, subtotal)
            s.executeUpdate()
        }
    }

    fun insertPayment(
        c: Connection,
        paymentId: String,
        transactionId: String,
        method: PaymentMethod,
        amountPaid: Long,
        change: Long,
        status: PaymentStatus = PaymentStatus.SUCCESS
    ) {
        val sql = """
            INSERT INTO pembayaran
            (id, transaksi_id, metode, nominal_bayar, kembalian, status, dibuat_pada)
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        """.trimIndent()
        c.prepareStatement(sql).use { s ->
            s.setString(1, paymentId)
            s.setString(2, transactionId)
            s.setString(3, method.name)
            s.setLong(4, amountPaid)
            s.setLong(5, change)
            s.setString(6, status.name)
            s.executeUpdate()
        }
    }

    fun getTransactionHistory(limit: Int = 100): List<TransactionHistoryItemDto> {
        require(limit in 1..1000) { "Limit harus antara 1 sampai 1000." }
        val sql = """
            SELECT t.id, t.nomor_transaksi, t.dibuat_pada, t.kasir_id, t.pelanggan_id,
                   t.subtotal, t.diskon, t.total, p.metode, p.nominal_bayar, p.kembalian, p.status
            FROM transaksi t
            LEFT JOIN pembayaran p ON p.transaksi_id = t.id
            ORDER BY t.dibuat_pada DESC LIMIT ?
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setInt(1, limit)
                s.executeQuery().use { r ->
                    val rows = mutableListOf<TransactionHistoryItemDto>()
                    while (r.next()) {
                        rows += TransactionHistoryItemDto(
                            transactionId = r.getString("id"),
                            transactionDate = r.getTimestamp("dibuat_pada").toInstant().toString(),
                            cashierId = r.getString("kasir_id"),
                            customerId = r.getString("pelanggan_id"),
                            subtotal = r.getLong("subtotal"),
                            discountAmount = r.getLong("diskon"),
                            total = r.getLong("total"),
                            paymentMethod = r.getString("metode") ?: "CASH",
                            amountPaid = r.getLong("nominal_bayar"),
                            changeAmount = r.getLong("kembalian"),
                            paymentStatus = r.getString("status") ?: "SUCCESS"
                        )
                    }
                    return rows
                }
            }
        }
    }

    fun getSalesReport(from: Date, toExclusive: Date): List<SalesReportRowDto> {
        require(!from.after(toExclusive)) { "Rentang tanggal tidak valid." }
        val sql = """
            SELECT CAST(t.dibuat_pada AS DATE) as tanggal,
                   COUNT(DISTINCT t.id) as jumlah_transaksi,
                   COALESCE(SUM(t.subtotal), 0) as penjualan_kotor,
                   COALESCE(SUM(t.diskon), 0) as total_diskon,
                   COALESCE(SUM(t.total), 0) as penjualan_bersih
            FROM transaksi t
            JOIN pembayaran p ON p.transaksi_id = t.id
            WHERE t.dibuat_pada >= ? AND t.dibuat_pada < ? AND p.status = 'SUCCESS'
            GROUP BY CAST(t.dibuat_pada AS DATE)
            ORDER BY CAST(t.dibuat_pada AS DATE) ASC
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setDate(1, from)
                s.setDate(2, toExclusive)
                s.executeQuery().use { r ->
                    val rows = mutableListOf<SalesReportRowDto>()
                    while (r.next()) {
                        rows += SalesReportRowDto(
                            salesDate = r.getDate("tanggal").toString(),
                            transactionCount = r.getLong("jumlah_transaksi"),
                            grossSales = r.getLong("penjualan_kotor"),
                            discountTotal = r.getLong("total_diskon"),
                            netSales = r.getLong("penjualan_bersih")
                        )
                    }
                    return rows
                }
            }
        }
    }
}

