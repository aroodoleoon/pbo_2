package com.pos.pelanggan

// Tingkatan membership toko
enum class TingkatMember(val persentaseDiskon: Double) {
    REGULER(0.05),  // Diskon 5%
    SILVER(0.08),   // Diskon 8%
    GOLD(0.12)      // Diskon 12%
}

// -------------------------------------------------------------
// 1. KELAS PELANGGAN & MEMBER
// -------------------------------------------------------------

/**
 * Kelas Pelanggan biasa (Non-Member) sesuai Tabel 6.1 Dokumen Analisis POS.
 * Digunakan untuk mencatat data pembeli reguler yang tidak memiliki membership.
 */
open class Pelanggan(
    val idPelanggan: String,
    var nama: String,
    var kontak: String
) {
    open val isMember: Boolean = false

    open fun hitungDiskon(totalBelanja: Double): Double {
        // Pelanggan reguler non-member tidak memperoleh diskon member
        return 0.0
    }

    open fun tampilkanInfo() {
        println("ID Pelanggan : $idPelanggan")
        println("Nama         : $nama")
        println("Kontak       : $kontak")
        println("Status       : Non-Member (Pelanggan Reguler)")
    }
}

/**
 * Kelas Member: turunan dari Pelanggan.
 * Mengimplementasikan poin loyalty, level membership, dan kalkulasi diskon.
 */
class Member(
    idPelanggan: String,
    nama: String,
    kontak: String,
    val idMember: String,
    var poin: Int = 0,
    var tingkatMember: TingkatMember = TingkatMember.REGULER
) : Pelanggan(idPelanggan, nama, kontak) {

    override val isMember: Boolean = true

    override fun hitungDiskon(totalBelanja: Double): Double {
        return totalBelanja * tingkatMember.persentaseDiskon
    }

    // Menambah poin member: 1 poin tiap kelipatan Rp 10.000 belanja
    fun tambahPoin(totalBelanja: Double): Int {
        val poinDidapat = (totalBelanja / 10000).toInt()
        poin += poinDidapat
        perbaruiTingkatMember()
        return poinDidapat
    }

    // Menggunakan poin untuk potongan pembayaran (1 poin bernilai Rp 100)
    fun gunakanPoin(jumlahTukar: Int): Double {
        return if (jumlahTukar <= poin) {
            poin -= jumlahTukar
            val potongan = jumlahTukar * 100.0
            println("[Poin Digunakan] $jumlahTukar poin ditukar dengan potongan Rp $potongan.")
            potongan
        } else {
            println("[Poin Gagal] Poin tidak mencukupi (Tersedia: $poin, Diminta: $jumlahTukar).")
            0.0
        }
    }

    // Pembaruan level otomatis berdasarkan perolehan poin
    private fun perbaruiTingkatMember() {
        tingkatMember = when {
            poin >= 200 -> TingkatMember.GOLD
            poin >= 100 -> TingkatMember.SILVER
            else -> TingkatMember.REGULER
        }
    }

    override fun tampilkanInfo() {
        println("ID Pelanggan : $idPelanggan")
        println("ID Member    : $idMember")
        println("Nama         : $nama")
        println("Kontak       : $kontak")
        println("Status       : Member Resmi (${tingkatMember.name})")
        println("Poin Aktif   : $poin")
        println("Hak Diskon   : ${(tingkatMember.persentaseDiskon * 100).toInt()}%")
    }
}

// -------------------------------------------------------------
// 2. HASIL IDENTIFIKASI TRANSAKSI
// -------------------------------------------------------------

data class HasilIdentifikasiPelanggan(
    val idIdentitas: String,
    val namaPelanggan: String,
    val statusMember: Boolean,
    val totalAwal: Double,
    val diskonMember: Double,
    val totalAkhir: Double,
    val referensiMember: Member? = null
)

// -------------------------------------------------------------
// 3. PENGELOLAAN DATA PELANGGAN & PENDAFTARAN
// -------------------------------------------------------------

class PengelolaPelanggan {
    private val daftarPelanggan = mutableListOf<Pelanggan>()
    private var urutanMember: Int = 1

    fun daftarkanPelangganReguler(nama: String, kontak: String): Pelanggan {
        val idPelanggan = "PLG-${System.currentTimeMillis() % 10000}"
        val pelanggan = Pelanggan(idPelanggan, nama, kontak)
        daftarPelanggan.add(pelanggan)
        println("[Sukses] Pelanggan umum didaftarkan: ${pelanggan.nama} (ID: $idPelanggan)")
        return pelanggan
    }

    fun daftarkanMember(nama: String, kontak: String, poinAwal: Int = 0): Member {
        val idPelanggan = "PLG-${System.currentTimeMillis() % 10000}"
        val idMember = "MBR-${String.format("%04d", urutanMember++)}"

        val member = Member(
            idPelanggan = idPelanggan,
            nama = nama,
            kontak = kontak,
            idMember = idMember,
            poin = poinAwal
        )
        daftarPelanggan.add(member)
        println("[Sukses] Member baru terdaftar: ${member.nama} | ID Member: $idMember")
        return member
    }

    fun cariPelanggan(kunciCari: String): Pelanggan? {
        return daftarPelanggan.find {
            it.idPelanggan.equals(kunciCari, ignoreCase = true) ||
            it.kontak == kunciCari ||
            (it is Member && it.idMember.equals(kunciCari, ignoreCase = true))
        }
    }

    /**
     * Menangani identifikasi pelanggan saat transaksi kasir.
     * Sesuai Bagian 4.2 dan 7.2: Transaksi kasir mendukung non-member anonim (tanpa ID/akun)
     * maupun pencarian member via ID/No. HP.
     */
    fun prosesIdentifikasiTransaksi(kunciPelanggan: String?, nominalBelanja: Double): HasilIdentifikasiPelanggan {
        // Kasus 1: Pelanggan non-member anonim (tidak membawa / tidak ingin mencatat kartu)
        if (kunciPelanggan.isNullOrBlank()) {
            return HasilIdentifikasiPelanggan(
                idIdentitas = "NON-MEMBER-ANONIM",
                namaPelanggan = "Pelanggan Umum (Tamu)",
                statusMember = false,
                totalAwal = nominalBelanja,
                diskonMember = 0.0,
                totalAkhir = nominalBelanja,
                referensiMember = null
            )
        }

        // Kasus 2: Pelanggan dicari melalui ID / No HP
        val pelanggan = cariPelanggan(kunciPelanggan)

        return if (pelanggan is Member) {
            val diskon = pelanggan.hitungDiskon(nominalBelanja)
            HasilIdentifikasiPelanggan(
                idIdentitas = pelanggan.idMember,
                namaPelanggan = pelanggan.nama,
                statusMember = true,
                totalAwal = nominalBelanja,
                diskonMember = diskon,
                totalAkhir = nominalBelanja - diskon,
                referensiMember = pelanggan
            )
        } else if (pelanggan != null) {
            HasilIdentifikasiPelanggan(
                idIdentitas = pelanggan.idPelanggan,
                namaPelanggan = pelanggan.nama,
                statusMember = false,
                totalAwal = nominalBelanja,
                diskonMember = 0.0,
                totalAkhir = nominalBelanja,
                referensiMember = null
            )
        } else {
            // Jika ID tidak ditemukan di sistem, transaksi tetap dapat dilanjutkan sebagai non-member
            println("[Perhatian] ID/Kontak '$kunciPelanggan' tidak ditemukan. Diperlakukan sebagai Non-Member.")
            HasilIdentifikasiPelanggan(
                idIdentitas = "TIDAK-TERDAFTAR",
                namaPelanggan = "Pelanggan Non-Member",
                statusMember = false,
                totalAwal = nominalBelanja,
                diskonMember = 0.0,
                totalAkhir = nominalBelanja,
                referensiMember = null
            )
        }
    }

    fun tampilkanSemuaPelanggan() {
        println("\n================= DAFTAR DATA PELANGGAN =================")
        if (daftarPelanggan.isEmpty()) {
            println("Belum ada data pelanggan.")
            return
        }
        daftarPelanggan.forEach {
            it.tampilkanInfo()
            println("-------------------------------------------------------")
        }
    }
}