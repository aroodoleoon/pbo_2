// Status transaksi untuk aturan bisnis pembatalan / penyelesaian
enum class StatusTransaksi {
    PENDING, BERHASIL, DIBATALKAN
}

// Representasi Item Detail Transaksi
class DetailTransaksi(
    val barang: Barang, // Mengambil dari modul Anggota 2
    val jumlahBeli: Int
) {
    // Snapshot harga saat transaksi dilakukan (sesuai aturan bisnis dokumen)
    val hargaSaatTransaksi: Double = barang.hargaJual
    val subtotal: Double = hitungSubtotal()

    fun hitungSubtotal(): Double {
        return hargaSaatTransaksi * jumlahBeli
    }
}

// Kelas Utama Transaksi POS
class Transaksi(
    val idTransaksi: String,
    val kasir: Karyawan, // Mengambil dari modul Anggota 4
    val pelanggan: Pelanggan? = null // Bisa null jika non-member (anonim)
) {
    val tanggal: java.util.Date = java.util.Date()
    val daftarItem: MutableList<DetailTransaksi> = mutableListOf()
    var total: Double = 0.0
        private set
    var status: StatusTransaksi = StatusTransaksi.PENDING
        private set

    // Menambahkan item ke keranjang belanja
    fun tambahItem(barang: Barang, jumlah: Int) {
        // Validasi stok sebelum dimasukkan ke keranjang
        if (jumlah > barang.stok) {
            throw IllegalArgumentException("Stok tidak mencukupi! Stok tersedia: ${barang.stok}")
        }
        if (jumlah <= 0) {
            throw IllegalArgumentException("Jumlah pembelian harus lebih dari 0.")
        }

        val detail = DetailTransaksi(barang, jumlah)
        daftarItem.add(detail)
        hitungTotal()
    }

    // Menghitung total belanja keseluruhan (termasuk diskon member jika ada)
    fun hitungTotal() {
        var subtotalKeseluruhan = daftarItem.sumOf { it.subtotal }

        // Contoh penerapan polimorfisme/diskon jika pelanggan adalah Member (Anggota 4)
        if (pelanggan is Member) {
            // Memanggil fungsi hitungDiskon dari kelas Member
            val diskon = pelanggan.hitungDiskon(subtotalKeseluruhan)
            subtotalKeseluruhan -= diskon
        }

        this.total = subtotalKeseluruhan
    }

    // Menyelesaikan transaksi dan memvalidasi stok akhir
    fun selesaikanTransaksi() {
        if (daftarItem.isEmpty()) {
            throw IllegalStateException("Keranjang transaksi masih kosong.")
        }

        // Validasi ulang stok semua item sebelum dikurangi
        for (item in daftarItem) {
            if (item.jumlahBeli > item.barang.stok) {
                throw IllegalStateException("Transaksi gagal: Stok ${item.barang.nama} tidak mencukupi.")
            }
        }

        // Kurangi stok barang (melalui method di modul inventaris - Anggota 2)
        for (item in daftarItem) {
            item.barang.kurangiStok(item.jumlahBeli)
        }

        this.status = StatusTransaksi.BERHASIL
    }

    // Membatalkan transaksi (mengembalikan stok jika diperlukan)
    fun batalkanTransaksi() {
        if (this.status == StatusTransaksi.BERHASIL) {
            // Kembalikan stok jika transaksi sudah terlanjur sukses
            for (item in daftarItem) {
                item.barang.tambahStok(item.jumlahBeli)
            }
        }
        this.status = StatusTransaksi.DIBATALKAN
    }
}