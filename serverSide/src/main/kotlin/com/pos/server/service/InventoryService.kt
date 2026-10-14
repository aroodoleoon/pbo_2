package com.pos.server.service

import com.pos.server.domain.enums.StockMovementType
import com.pos.server.domain.exception.StokTidakCukupException
import com.pos.server.domain.exception.ValidasiException
import com.pos.server.domain.model.Barang
import com.pos.server.domain.model.PergerakanStok
import com.pos.server.dto.sales.SaleItemRequest
import com.pos.server.repository.BarangRepository
import com.pos.server.repository.PergerakanStokRepository
import java.sql.Connection
import java.time.Instant
import java.util.UUID

/**
 * Snapshot baris barang untuk transaksi penjualan (kontrak integrasi Modul 2 Alex & Modul 5 Josua).
 */
data class SaleLineSnapshot(
    val itemId: String,
    val itemName: String,
    val quantity: Int,
    val unitPrice: Long
) {
    val subtotal: Long get() = Math.multiplyExact(unitPrice, quantity.toLong())
}

/**
 * Interface StockGateway yang dibutuhkan Modul 5 Josua untuk transaksi atomik.
 */
interface StockGateway {
    fun prepareSale(connection: Connection, items: List<SaleItemRequest>): List<SaleLineSnapshot>
    fun applySale(connection: Connection, transactionId: String, cashierId: String, items: List<SaleLineSnapshot>)
}

/**
 * Layanan Inventaris Barang: SATU-SATUNYA sumber logika stok (Modul 2 Alex).
 * Mengimplementasikan StockGateway untuk terhubung langsung dengan PaymentService Josua.
 */
class InventoryService(
    private val barangRepository: BarangRepository,
    private val pergerakanStokRepository: PergerakanStokRepository
) : StockGateway {

    // ================= KONTRAK INTEGRASI STOCK GATEWAY (MODUL 5 JOSUA) =================

    override fun prepareSale(connection: Connection, items: List<SaleItemRequest>): List<SaleLineSnapshot> {
        val snapshots = mutableListOf<SaleLineSnapshot>()
        for (item in items) {
            val barang = barangRepository.findByIdWithConnection(connection, item.productId)
                ?: throw ValidasiException("Barang dengan ID '${item.productId}' tidak ditemukan.")

            if (!barang.aktif) {
                throw ValidasiException("Barang '${barang.nama}' berstatus nonaktif dan tidak bisa dijual.")
            }
            if (barang.stok < item.quantity) {
                throw StokTidakCukupException(
                    "Stok barang '${barang.nama}' tidak mencukupi (tersedia: ${barang.stok}, diminta: ${item.quantity})."
                )
            }
            snapshots.add(
                SaleLineSnapshot(
                    itemId = barang.id,
                    itemName = barang.nama,
                    quantity = item.quantity,
                    unitPrice = barang.hargaJual
                )
            )
        }
        return snapshots
    }

    override fun applySale(
        connection: Connection,
        transactionId: String,
        cashierId: String,
        items: List<SaleLineSnapshot>
    ) {
        for (item in items) {
            val barang = barangRepository.findByIdWithConnection(connection, item.itemId)
                ?: throw ValidasiException("Barang dengan ID '${item.itemId}' tidak ditemukan saat pemotongan stok.")

            barang.kurangiStok(item.quantity)
            barangRepository.updateStock(connection, barang.id, barang.stok)

            val pergerakan = PergerakanStok(
                barangId = barang.id,
                jenis = StockMovementType.OUT_SALE,
                jumlah = item.quantity,
                alasan = "Penjualan transaksi $transactionId",
                karyawanId = cashierId,
                referensi = transactionId
            )
            pergerakanStokRepository.insert(connection, pergerakan)
        }
    }

    // ================= LOGIKA INVENTARIS UTAMA (MODUL 2 ALEX) =================

    fun cekStokTersedia(barangId: String, jumlah: Int): Boolean {
        val barang = barangRepository.findById(barangId) ?: return false
        return barang.stokCukup(jumlah)
    }

    fun ambilHargaJual(barangId: String): Long {
        return barangRepository.findByIdOrThrow(barangId).hargaJual
    }

    fun barangMasuk(
        barangId: String,
        jumlah: Int,
        sumber: String,
        karyawanId: String,
        alasan: String? = null
    ): Barang {
        if (sumber.isBlank()) throw ValidasiException("Sumber barang masuk wajib diisi.")
        if (jumlah <= 0) throw ValidasiException("Jumlah barang masuk harus > 0.")

        val barang = barangRepository.findByIdOrThrow(barangId)
        barang.tambahStok(jumlah)
        barangRepository.update(barang)

        // Catat pergerakan stok
        dataSourceConnection().use { c ->
            val pergerakan = PergerakanStok(
                barangId = barang.id,
                jenis = StockMovementType.IN,
                jumlah = jumlah,
                alasan = alasan ?: "Penerimaan dari $sumber",
                karyawanId = karyawanId
            )
            pergerakanStokRepository.insert(c, pergerakan)
        }
        return barang
    }

    fun barangKeluar(
        barangId: String,
        jumlah: Int,
        jenis: StockMovementType,
        alasan: String,
        karyawanId: String
    ): Barang {
        if (alasan.isBlank()) throw ValidasiException("Alasan barang keluar wajib diisi.")
        if (jumlah <= 0) throw ValidasiException("Jumlah barang keluar harus > 0.")
        if (jenis.menambah) throw ValidasiException("Jenis pergerakan harus berupa pengurangan stok.")

        val barang = barangRepository.findByIdOrThrow(barangId)
        barang.kurangiStok(jumlah)
        barangRepository.update(barang)

        dataSourceConnection().use { c ->
            val pergerakan = PergerakanStok(
                barangId = barang.id,
                jenis = jenis,
                jumlah = jumlah,
                alasan = alasan.trim(),
                karyawanId = karyawanId
            )
            pergerakanStokRepository.insert(c, pergerakan)
        }
        return barang
    }

    fun daftarStokMenipis(): List<Barang> {
        return barangRepository.findAll(onlyActive = true).filter { it.stokMenipis() }
    }

    fun riwayatPergerakan(productId: String?, from: Instant?, to: Instant?): List<PergerakanStok> {
        return pergerakanStokRepository.findMovements(productId, from, to)
    }

    private fun dataSourceConnection(): Connection {
        // Mendapatkan koneksi baru untuk pencatatan mandiri
        val field = BarangRepository::class.java.getDeclaredField("dataSource")
        field.isAccessible = true
        val ds = field.get(barangRepository) as javax.sql.DataSource
        return ds.connection
    }
}

