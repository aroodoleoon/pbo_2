package com.pos.server.domain

import com.pos.server.domain.exception.StokTidakCukupException
import com.pos.server.domain.exception.ValidasiException
import com.pos.server.domain.model.Barang
import kotlin.test.*

class BarangTest {

    @Test
    fun `stok barang bertambah dan berkurang dengan benar`() {
        val barang = Barang(
            kode = "8991234567890",
            nama = "Kopi Susu Kemasan",
            hargaBeli = 3000L,
            hargaJual = 5000L,
            stokAwal = 10,
            stokMinimum = 3
        )

        assertEquals(10, barang.stok)

        barang.tambahStok(5)
        assertEquals(15, barang.stok)

        barang.kurangiStok(4)
        assertEquals(11, barang.stok)
    }

    @Test
    fun `pengurangan stok melebihi stok yang ada melempar StokTidakCukupException`() {
        val barang = Barang(
            kode = "8991234567891",
            nama = "Air Mineral 600ml",
            hargaBeli = 2000L,
            hargaJual = 3500L,
            stokAwal = 5
        )

        val exception = assertFailsWith<StokTidakCukupException> {
            barang.kurangiStok(6)
        }
        assertTrue(exception.message!!.contains("tidak mencukupi"))
    }

    @Test
    fun `deteksi stok menipis bekerja sesuai batas stok minimum`() {
        val barang = Barang(
            kode = "8991234567892",
            nama = "Roti Cokelat",
            hargaBeli = 4000L,
            hargaJual = 6000L,
            stokAwal = 3,
            stokMinimum = 3
        )

        assertTrue(barang.stokMenipis())
        barang.tambahStok(1)
        assertFalse(barang.stokMenipis())
    }

    @Test
    fun `validasi harga dan nama tidak boleh kosong atau negatif`() {
        assertFailsWith<ValidasiException> {
            Barang(
                kode = "8991234567893",
                nama = "   ",
                hargaBeli = 1000L,
                hargaJual = 2000L
            )
        }
    }
}

