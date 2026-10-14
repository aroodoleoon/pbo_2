package com.pos.server.service

import com.pos.server.domain.exception.ValidasiException
import com.pos.server.dto.sales.SalesReportRowDto
import com.pos.server.dto.sales.TransactionHistoryItemDto
import com.pos.server.repository.TransaksiRepository
import java.sql.Date
import java.time.LocalDate

/**
 * Layanan Laporan Penjualan dan Riwayat Transaksi (Modul 5 Josua).
 */
class ReportService(
    private val transaksiRepository: TransaksiRepository
) {
    fun getTransactionHistory(limit: Int = 100): List<TransactionHistoryItemDto> {
        return transaksiRepository.getTransactionHistory(limit)
    }

    fun getSalesReport(fromDateStr: String, toDateStr: String): List<SalesReportRowDto> {
        val fromDate = try {
            LocalDate.parse(fromDateStr)
        } catch (e: Exception) {
            throw ValidasiException("Format tanggal awal tidak valid. Gunakan YYYY-MM-DD.")
        }

        val toDate = try {
            LocalDate.parse(toDateStr)
        } catch (e: Exception) {
            throw ValidasiException("Format tanggal akhir tidak valid. Gunakan YYYY-MM-DD.")
        }

        if (toDate.isBefore(fromDate)) {
            throw ValidasiException("Tanggal akhir tidak boleh lebih awal dari tanggal awal.")
        }

        // Batas akhir SQL eksklusif, tambah 1 hari
        return transaksiRepository.getSalesReport(
            Date.valueOf(fromDate),
            Date.valueOf(toDate.plusDays(1))
        )
    }
}

