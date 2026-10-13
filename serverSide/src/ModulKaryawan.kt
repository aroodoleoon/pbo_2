package com.pos.karyawan

import java.security.MessageDigest

// Status aktif karyawan sesuai aturan bisnis
enum class StatusKaryawan {
    AKTIF,
    NONAKTIF
}

// Peran akun karyawan
enum class PeranKaryawan {
    ADMIN,
    KASIR
}

// -------------------------------------------------------------
// 1. KELAS KARYAWAN & HIERARKI HAK AKSES
// -------------------------------------------------------------

/**
 * Kelas abstrak dasar Karyawan sesuai Tabel 6.1 Dokumen Analisis POS.
 * Menyimpan password dalam bentuk hash untuk memenuhi kebutuhan nonfungsional keamanan.
 */
abstract class Karyawan(
    val idKaryawan: String,
    var nama: String,
    val username: String,
    var passwordHash: String,
    var status: StatusKaryawan = StatusKaryawan.AKTIF,
    val peran: PeranKaryawan
) {
    // Memverifikasi apakah kata sandi input cocok dengan hash yang tersimpan
    fun verifikasiPassword(kataSandiInput: String): Boolean {
        val hashInput = hitungHash(kataSandiInput)
        return this.passwordHash == hashInput
    }

    // Mengubah kata sandi dengan hashing SHA-256
    fun ubahPassword(kataSandiBaru: String) {
        this.passwordHash = hitungHash(kataSandiBaru)
    }

    // Menampilkan informasi dasar karyawan
    open fun tampilkanInfo() {
        println("ID Karyawan : $idKaryawan")
        println("Nama        : $nama")
        println("Username    : $username")
        println("Peran       : $peran")
        println("Status      : $status")
    }

    // Kontrak hak akses yang harus didefinisikan oleh tiap peran turunan
    abstract fun memilikiAkses(fitur: String): Boolean
    abstract fun tampilkanHakAkses()

    companion object {
        // Enkripsi kata sandi menggunakan algoritma SHA-256
        fun hitungHash(input: String): String {
            val digest = MessageDigest.getInstance("SHA-256")
            val hashBytes = digest.digest(input.toByteArray(Charsets.UTF_8))
            return hashBytes.joinToString("") { "%02x".format(it) }
        }
    }
}

/**
 * Kelas Admin: mewarisi Karyawan.
 * Bertanggung jawab mengelola master data (karyawan, barang, pelanggan) dan laporan.
 */
class Admin(
    idKaryawan: String,
    nama: String,
    username: String,
    passwordHash: String,
    status: StatusKaryawan = StatusKaryawan.AKTIF
) : Karyawan(idKaryawan, nama, username, passwordHash, status, PeranKaryawan.ADMIN) {

    override fun memilikiAkses(fitur: String): Boolean {
        // Admin memiliki akses penuh ke seluruh fitur sistem
        return true
    }

    override fun tampilkanHakAkses() {
        println("Hak Akses [Admin]: Akses Penuh (Kelola Barang, Stok, Karyawan, Pelanggan, dan Laporan).")
    }
}

/**
 * Kelas Kasir: mewarisi Karyawan.
 * Bertanggung jawab melayani transaksi POS, pembayaran, dan pencarian member.
 */
class Kasir(
    idKaryawan: String,
    nama: String,
    username: String,
    passwordHash: String,
    status: StatusKaryawan = StatusKaryawan.AKTIF
) : Karyawan(idKaryawan, nama, username, passwordHash, status, PeranKaryawan.KASIR) {

    override fun memilikiAkses(fitur: String): Boolean {
        return when (fitur.uppercase()) {
            "TRANSAKSI_POS", "PROSES_PEMBAYARAN", "CARI_PELANGGAN", "CETAK_STRUK" -> true
            else -> false // Dibatasi dari fitur master data barang/karyawan dan laporan keuangan
        }
    }

    override fun tampilkanHakAkses() {
        println("Hak Akses [Kasir]: Terbatas pada Transaksi POS, Pembayaran, dan Pencarian Pelanggan.")
    }
}

// -------------------------------------------------------------
// 2. PENGELOLAAN DATA KARYAWAN (REPOSITORY)
// -------------------------------------------------------------

class PengelolaKaryawan {
    private val daftarKaryawan = mutableListOf<Karyawan>()

    fun tambahKaryawan(karyawan: Karyawan): Boolean {
        if (daftarKaryawan.any { it.username.equals(karyawan.username, ignoreCase = true) }) {
            println("[Gagal] Username '${karyawan.username}' sudah terdaftar.")
            return false
        }
        daftarKaryawan.add(karyawan)
        println("[Sukses] Karyawan ${karyawan.nama} (${karyawan.peran}) berhasil ditambahkan.")
        return true
    }

    fun cariBerdasarkanUsername(username: String): Karyawan? {
        return daftarKaryawan.find { it.username.equals(username, ignoreCase = true) }
    }

    fun cariBerdasarkanId(idKaryawan: String): Karyawan? {
        return daftarKaryawan.find { it.idKaryawan.equals(idKaryawan, ignoreCase = true) }
    }

    fun ubahStatusKaryawan(idKaryawan: String, statusBaru: StatusKaryawan): Boolean {
        val karyawan = cariBerdasarkanId(idKaryawan)
        return if (karyawan != null) {
            karyawan.status = statusBaru
            println("[Info] Status karyawan ${karyawan.nama} diubah menjadi $statusBaru.")
            true
        } else {
            println("[Gagal] Karyawan dengan ID '$idKaryawan' tidak ditemukan.")
            false
        }
    }

    fun tampilkanSemuaKaryawan() {
        println("\n================= DAFTAR DATA KARYAWAN =================")
        if (daftarKaryawan.isEmpty()) {
            println("Belum ada data karyawan.")
            return
        }
        daftarKaryawan.forEach {
            it.tampilkanInfo()
            println("-------------------------------------------------------")
        }
    }
}

// -------------------------------------------------------------
// 3. SISTEM AUTENTIKASI DAN KONTROL AKSES
// -------------------------------------------------------------

class LayananAutentikasi(private val pengelolaKaryawan: PengelolaKaryawan) {
    var penggunaAktif: Karyawan? = null
        private set

    fun login(username: String, kataSandiPlaintxt: String): Boolean {
        val karyawan = pengelolaKaryawan.cariBerdasarkanUsername(username)

        if (karyawan == null) {
            println("[Login Gagal] Pengguna '$username' tidak ditemukan.")
            return false
        }

        if (karyawan.status == StatusKaryawan.NONAKTIF) {
            println("[Login Gagal] Akun sedang dinonaktifkan. Hubungi Admin.")
            return false
        }

        return if (karyawan.verifikasiPassword(kataSandiPlaintxt)) {
            penggunaAktif = karyawan
            println("\n[Login Berhasil] Selamat datang, ${karyawan.nama} (${karyawan.peran})")
            karyawan.tampilkanHakAkses()
            true
        } else {
            println("[Login Gagal] Kata sandi tidak cocok.")
            false
        }
    }

    fun logout() {
        if (penggunaAktif != null) {
            println("\n[Logout] Pengguna '${penggunaAktif?.nama}' telah keluar dari sesi.")
            penggunaAktif = null
        } else {
            println("[Info] Tidak ada sesi aktif.")
        }
    }

    // Memvalidasi apakah sesi yang sedang aktif diizinkan membuka fitur tertentu
    fun validasiHakAkses(namaFitur: String): Boolean {
        val user = penggunaAktif
        if (user == null) {
            println("[Akses Ditolak] Silakan login terlebih dahulu.")
            return false
        }

        val diizinkan = user.memilikiAkses(namaFitur)
        if (!diizinkan) {
            println("[Akses Ditolak] Peran '${user.peran}' tidak memiliki izin untuk fitur: $namaFitur.")
        }
        return diizinkan
    }
}