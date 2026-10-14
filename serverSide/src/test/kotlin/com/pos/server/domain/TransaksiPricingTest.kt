package com.pos.server.domain

import com.pos.server.domain.model.Barang
import com.pos.server.domain.model.Member
import com.pos.server.domain.model.Pelanggan
import com.pos.server.domain.model.TingkatMember
import com.pos.server.domain.model.Transaksi
import kotlin.test.*

class TransaksiPricingTest {

    private val barang1 = Barang(
        kode = "899001",
        nama = "Snack Kentang",
        hargaBeli = 7000L,
        hargaJual = 10000L,
        stokAwal = 20
    )

    private val barang2 = Barang(
        kode = "899002",
        nama = "Teh Kotak",
        hargaBeli = 3000L,
        hargaJual = 5000L,
        stokAwal = 30
    )

    @Test
    fun `transaksi tanpa member menghitung subtotal dan total secara tepat`() {
        val nonMember = Pelanggan(nama = "Budi Reguler", kontak = "08123456789")
        val transaksi = Transaksi(
            kasirId = "KASIR-01",
            pelangganId = nonMember.id,
            pelanggan = nonMember
        )

        transaksi.tambahItem(barang1, 2) // 2 * 10.000 = 20.000
        transaksi.tambahItem(barang2, 3) // 3 * 5.000 = 15.000

        assertEquals(35000L, transaksi.subtotal)
        assertEquals(0L, transaksi.diskon)
        assertEquals(35000L, transaksi.total)
    }

    @Test
    fun `transaksi member mendapatkan diskon polimorfisme loyalty sesuai level`() {
        val memberSilver = Member(
            nama = "Siti Member",
            kontak = "08198765432",
            nomorMember = "MBR-0001",
            poin = 120,
            tingkatMember = TingkatMember.SILVER // 8% diskon
        )

        val transaksi = Transaksi(
            kasirId = "KASIR-01",
            pelangganId = memberSilver.id,
            pelanggan = memberSilver
        )

        transaksi.tambahItem(barang1, 10) // 10 * 10.000 = 100.000

        assertEquals(100000L, transaksi.subtotal)
        assertEquals(8000L, transaksi.diskon) // 8% dari 100.000
        assertEquals(92000L, transaksi.total)
    }

    @Test
    fun `perubahan harga barang setelah transaksi tidak mengubah snapshot harga transaksi`() {
        val transaksi = Transaksi(kasirId = "KASIR-01")
        transaksi.tambahItem(barang1, 2) // 2 * 10.000 = 20.000

        assertEquals(20000L, transaksi.total)

        // Ubah harga barang di master data
        barang1.ubahHarga(hargaBeliBaru = 9000L, hargaJualBaru = 15000L)

        // Snapshot harga saat transaksi tidak boleh berubah
        assertEquals(10000L, transaksi.daftarItem[0].hargaSaatTransaksi)
        assertEquals(20000L, transaksi.total)
    }
}

