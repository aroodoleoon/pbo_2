package com.pos.server.service

import com.pos.server.domain.exception.ValidasiException
import com.pos.server.domain.model.DetailTransaksi
import com.pos.server.domain.model.Member
import com.pos.server.domain.model.Transaksi
import com.pos.server.domain.policy.AturanPajak
import com.pos.server.dto.sales.CreateSaleRequest
import com.pos.server.dto.sales.SaleItemResponse
import com.pos.server.dto.sales.SaleResponse
import com.pos.server.repository.BarangRepository
import com.pos.server.repository.PelangganRepository
import com.pos.server.repository.TransaksiRepository
import java.util.UUID

/**
 * Layanan Transaksi POS (Modul 3 Dapin).
 * Mengatur keranjang belanja, kalkulasi diskon member & pajak PPN, dan snapshot harga.
 */
class SalesService(
    private val barangRepository: BarangRepository,
    private val pelangganRepository: PelangganRepository,
    private val transaksiRepository: TransaksiRepository,
    private val aturanPajak: AturanPajak = AturanPajak()
) {
    fun createSale(cashierId: String, request: CreateSaleRequest): SaleResponse {
        if (request.items.isEmpty()) {
            throw ValidasiException("Transaksi harus memiliki minimal satu barang belanjaan.")
        }

        val pelanggan = if (!request.customerId.isNullOrBlank()) {
            pelangganRepository.findById(request.customerId)
        } else null

        val transactionId = request.transactionId ?: UUID.randomUUID().toString()
        val transaction = Transaksi(
            id = transactionId,
            kasirId = cashierId,
            pelangganId = request.customerId,
            pelanggan = pelanggan,
            diskon = request.discountAmount
        )

        for (itemReq in request.items) {
            val barang = barangRepository.findByIdOrThrow(itemReq.productId)
            transaction.tambahItem(barang, itemReq.quantity)
        }

        // Hitung total dengan pajak PPN
        transaction.hitungTotal()
        val dasarPajak = if (transaction.subtotal > transaction.diskon) transaction.subtotal - transaction.diskon else 0L
        transaction.pajak = aturanPajak.hitungPajak(dasarPajak)
        transaction.hitungTotal()

        return mapToResponse(transaction)
    }

    private fun mapToResponse(transaksi: Transaksi): SaleResponse {
        return SaleResponse(
            id = transaksi.id,
            transactionNumber = transaksi.nomorTransaksi,
            cashierId = transaksi.kasirId,
            customerId = transaksi.pelangganId,
            subtotal = transaksi.subtotal,
            discount = transaksi.diskon,
            tax = transaksi.pajak,
            total = transaksi.total,
            discountDescription = transaksi.keteranganSyaratDiskon,
            status = transaksi.status.name,
            createdAt = transaksi.dibuatPada.toString(),
            items = transaksi.daftarItem.map { item ->
                SaleItemResponse(
                    id = item.id,
                    productId = item.barang.id,
                    productName = item.barang.nama,
                    quantity = item.jumlah,
                    unitPrice = item.hargaSaatTransaksi,
                    subtotal = item.subtotal
                )
            }
        )
    }
}

