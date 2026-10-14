package com.pos.server.domain.model

import com.pos.server.domain.exception.StokTidakCukupException
import com.pos.server.domain.exception.ValidasiException
import java.util.UUID

/**
 * Entitas domain Barang mengadopsi rancangan Modul 2 (Alex).
 * Menerapkan enkapsulasi: stok dan harga hanya dapat diubah melalui metode tervalidasi.
 */
class Barang(
    val id: String = UUID.randomUUID().toString(),
    kode: String,
    nama: String,
    var kategoriId: String? = null,
    hargaBeli: Long,
    hargaJual: Long,
    stokAwal: Int = 0,
    stokMinimum: Int = 0,
    var aktif: Boolean = true
) {
    var kode: String = kode.trim()
        set(value) {
            val v = value.trim()
            if (v.isEmpty()) throw ValidasiException("Kode barcode barang tidak boleh kosong.")
            field = v
        }

    var nama: String = nama.trim()
        set(value) {
            val v = value.trim()
            if (v.isEmpty()) throw ValidasiException("Nama barang tidak boleh kosong.")
            field = v
        }

    var hargaBeli: Long = 0L
        private set

    var hargaJual: Long = 0L
        private set

    var stok: Int = 0
        private set

    var stokMinimum: Int = 0
        set(value) {
            if (value < 0) throw ValidasiException("Stok minimum harus >= 0.")
            field = value
        }

    init {
        if (kode.trim().isEmpty()) throw ValidasiException("Kode barcode barang tidak boleh kosong.")
        if (nama.trim().isEmpty()) throw ValidasiException("Nama barang tidak boleh kosong.")
        ubahHarga(hargaBeli, hargaJual)
        if (stokAwal < 0) throw ValidasiException("Stok awal tidak boleh negatif.")
        this.stok = stokAwal
        this.stokMinimum = stokMinimum
    }

    fun tambahStok(jumlah: Int) {
        if (jumlah <= 0) throw ValidasiException("Jumlah penambahan stok harus > 0.")
        this.stok = Math.addExact(this.stok, jumlah)
    }

    fun kurangiStok(jumlah: Int) {
        if (jumlah <= 0) throw ValidasiException("Jumlah pengurangan stok harus > 0.")
        if (jumlah > this.stok) {
            throw StokTidakCukupException("Stok '$nama' tidak mencukupi (tersedia: $stok, diminta: $jumlah).")
        }
        this.stok -= jumlah
    }

    fun ubahHarga(hargaBeliBaru: Long, hargaJualBaru: Long) {
        if (hargaBeliBaru < 0) throw ValidasiException("Harga beli tidak boleh negatif.")
        if (hargaJualBaru < 0) throw ValidasiException("Harga jual tidak boleh negatif.")
        this.hargaBeli = hargaBeliBaru
        this.hargaJual = hargaJualBaru
    }

    fun stokCukup(jumlah: Int): Boolean {
        return aktif && jumlah > 0 && jumlah <= stok
    }

    fun stokMenipis(): Boolean {
        return stok <= stokMinimum
    }

    fun nonaktifkan() {
        this.aktif = false
    }

    fun aktifkan() {
        this.aktif = true
    }
}

