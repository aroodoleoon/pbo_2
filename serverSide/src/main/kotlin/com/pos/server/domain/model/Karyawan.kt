package com.pos.server.domain.model

import com.pos.server.domain.enums.Role
import com.pos.server.domain.exception.ValidasiException
import java.security.MessageDigest
import java.util.UUID

/**
 * Model hierarki Karyawan mengadopsi rancangan Modul 4 (Dhia).
 * Menggunakan enkapsulasi dan polimorfisme hak akses.
 */
abstract class Karyawan(
    val id: String = UUID.randomUUID().toString(),
    var nama: String,
    val username: String,
    var passwordHash: String,
    val role: Role,
    var aktif: Boolean = true
) {
    init {
        if (nama.trim().isEmpty()) throw ValidasiException("Nama karyawan tidak boleh kosong.")
        if (username.trim().isEmpty()) throw ValidasiException("Username tidak boleh kosong.")
        if (passwordHash.trim().isEmpty()) throw ValidasiException("Password hash tidak boleh kosong.")
    }

    fun verifikasiPassword(kataSandiInput: String): Boolean {
        val hashInput = hitungHash(kataSandiInput)
        return this.passwordHash == hashInput
    }

    fun ubahPassword(kataSandiBaru: String) {
        if (kataSandiBaru.trim().isEmpty()) throw ValidasiException("Password baru tidak boleh kosong.")
        this.passwordHash = hitungHash(kataSandiBaru)
    }

    abstract fun memilikiAkses(fitur: String): Boolean

    companion object {
        fun hitungHash(input: String): String {
            val digest = MessageDigest.getInstance("SHA-256")
            val hashBytes = digest.digest(input.toByteArray(Charsets.UTF_8))
            return hashBytes.joinToString("") { "%02x".format(it) }
        }
    }
}

class Admin(
    id: String = UUID.randomUUID().toString(),
    nama: String,
    username: String,
    passwordHash: String,
    aktif: Boolean = true
) : Karyawan(id, nama, username, passwordHash, Role.ADMIN, aktif) {
    override fun memilikiAkses(fitur: String): Boolean = true
}

class Kasir(
    id: String = UUID.randomUUID().toString(),
    nama: String,
    username: String,
    passwordHash: String,
    aktif: Boolean = true
) : Karyawan(id, nama, username, passwordHash, Role.CASHIER, aktif) {
    override fun memilikiAkses(fitur: String): Boolean {
        return when (fitur.uppercase()) {
            "TRANSAKSI_POS", "PROSES_PEMBAYARAN", "CARI_PELANGGAN", "DAFTAR_PELANGGAN", "CETAK_STRUK" -> true
            else -> false // Dibatasi dari kelola barang, hapus pelanggan, ubah harga, atau laporan keuangan
        }
    }
}

