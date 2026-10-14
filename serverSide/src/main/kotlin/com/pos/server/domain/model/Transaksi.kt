package com.pos.server.domain.model

import com.pos.server.domain.enums.TransactionStatus
import com.pos.server.domain.exception.ValidasiException
import java.time.Instant
import java.util.UUID

/**
 * Detail transaksi belanja (snapshot harga saat transaksi).
 * Menggabungkan ide Modul 3 (Dapin) dan Modul 5 (Josua).
 */
class DetailTransaksi(
    val id: String = UUID.randomUUID().toString(),
    val barang: Barang,
    jumlah: Int,
    val hargaSaatTransaksi: Long = barang.hargaJual
) {
    var jumlah: Int = jumlah
        private set

    val subtotal: Long
        get() = Math.multiplyExact(hargaSaatTransaksi, jumlah.toLong())

    init {
        if (jumlah <= 0) throw ValidasiException("Jumlah barang beli harus > 0.")
        if (hargaSaatTransaksi < 0) throw ValidasiException("Harga barang tidak valid.")
    }

    fun tambahJumlah(tambahan: Int) {
        if (tambahan <= 0) throw ValidasiException("Tambahan jumlah harus > 0.")
        jumlah += tambahan
    }
}

/**
 * Kelas Transaksi POS utama mengadopsi logika bisnis Modul 3 (Dapin) dan Modul 5 (Josua).
 */
class Transaksi(
    val id: String = UUID.randomUUID().toString(),
    val nomorTransaksi: String = "TRX-${System.currentTimeMillis()}",
    val kasirId: String,
    val pelangganId: String? = null,
    val pelanggan: Pelanggan? = null,
    var diskon: Long = 0L,
    var pajak: Long = 0L,
    var keteranganSyaratDiskon: String? = null,
    val dibuatPada: Instant = Instant.now()
) {
    private val _daftarItem = mutableListOf<DetailTransaksi>()
    val daftarItem: List<DetailTransaksi> get() = _daftarItem

    var subtotal: Long = 0L
        private set

    var total: Long = 0L
        private set

    var status: TransactionStatus = TransactionStatus.PENDING
        private set

    fun tambahItem(barang: Barang, jumlah: Int) {
        if (status != TransactionStatus.PENDING) {
            throw ValidasiException("Tidak dapat mengubah transaksi yang sudah selesai atau dibatalkan.")
        }
        if (!barang.stokCukup(jumlah)) {
            throw ValidasiException("Stok tidak mencukupi untuk '${barang.nama}' (tersedia: ${barang.stok}).")
        }

        val itemEksis = _daftarItem.find { it.barang.id == barang.id }
        if (itemEksis != null) {
            val totalJumlah = itemEksis.jumlah + jumlah
            if (totalJumlah > barang.stok) {
                throw ValidasiException("Stok tidak mencukupi untuk total pembelian ${barang.nama}.")
            }
            itemEksis.tambahJumlah(jumlah)
        } else {
            _daftarItem.add(DetailTransaksi(barang = barang, jumlah = jumlah))
        }

        hitungTotal()
    }

    fun hitungTotal() {
        subtotal = _daftarItem.fold(0L) { acc, item -> Math.addExact(acc, item.subtotal) }

        // Polimorfisme diskon loyalty pelanggan jika terdaftar sebagai Member (Modul 4)
        if (pelanggan is Member && diskon == 0L) {
            val diskonMember = pelanggan.hitungDiskon(subtotal)
            diskon = diskonMember
            if (diskonMember > 0) {
                keteranganSyaratDiskon = "Diskon Member ${pelanggan.tingkatMember.name} (${(pelanggan.tingkatMember.persentaseDiskon * 100).toInt()}%)"
            }
        }

        val setelahDiskon = if (subtotal > diskon) subtotal - diskon else 0L
        total = Math.addExact(setelahDiskon, pajak)
    }

    fun selesaikanTransaksi() {
        if (status != TransactionStatus.PENDING) {
            throw ValidasiException("Transaksi tidak dalam status PENDING.")
        }
        if (_daftarItem.isEmpty()) {
            throw ValidasiException("Keranjang belanja masih kosong.")
        }
        // Pastikan pengurangan stok dilakukan via pergerakan_stok / inventory service
        status = TransactionStatus.COMPLETED
    }

    fun batalkanTransaksi() {
        if (status == TransactionStatus.CANCELLED) {
            throw ValidasiException("Transaksi sudah dibatalkan sebelumnya.")
        }
        status = TransactionStatus.CANCELLED
    }
}

