import java.util.Date

// Representasi status transaksi yang aman dan eksplisit
enum class StatusTransaksi {
    PENDING, BERHASIL, DIBATALKAN
}

// Kelas Detail Transaksi dengan enkapsulasi data yang bersih
class DetailTransaksi(
    val barang: Barang,
    val jumlahBeli: Int
) {
    // Snapshot harga saat transaksi dilakukan (mencegah perubahan riwayat akibat fluktuasi harga master)
    val hargaSaatTransaksi: Double = barang.hargaJual

    // Subtotal dihitung secara otomatis dan konsisten
    val subtotal: Double
        get() = hargaSaatTransaksi * jumlahBeli
}

// Kelas Utama Transaksi POS
class Transaksi(
    val idTransaksi: String,
    val kasir: Karyawan,
    val pelanggan: Pelanggan? = null
) {
    val tanggal: Date = Date()

    // Menggunakan backing property untuk enkapsulasi mutable list (mencegah modifikasi langsung dari luar kelas)
    private val _daftarItem = mutableListOf<DetailTransaksi>()
    val daftarItem: List<DetailTransaksi>
        get() = _daftarItem

    var totalBelanja: Double = 0.0
        private set

    var totalDiskon: Double = 0.0
        private set

    var totalAkhir: Double = 0.0
        private set

    var status: StatusTransaksi = StatusTransaksi.PENDING
        private set

    /**
     * Menambahkan item atau menggabungkan kuantitas jika barang yang sama sudah ada di keranjang.
     */
    fun tambahItem(barang: Barang, jumlah: Int) {
        require(status == StatusTransaksi.PENDING) { "Tidak dapat mengubah item pada transaksi yang sudah ${status.name}." }
        require(jumlah > 0) { "Jumlah pembelian harus lebih dari 0." }

        // Cek apakah barang sudah ada di dalam keranjang
        val existingItem = _daftarItem.find { it.barang.idBarang == barang.idBarang }
        val totalPermintaan = (existingItem?.jumlahBeli ?: 0) + jumlah

        // Validasi ketersediaan stok secara real-time
        if (totalPermintaan > barang.stok) {
            throw IllegalArgumentException("Stok tidak mencukupi! Stok tersedia untuk '${barang.nama}': ${barang.stok}")
        }

        if (existingItem != null) {
            // Jika sudah ada, hapus yang lama dan buat objek baru dengan jumlah terakumulasi (immutable data pattern)
            _daftarItem.remove(existingItem)
            _daftarItem.add(DetailTransaksi(barang, totalPermintaan))
        } else {
            _daftarItem.add(DetailTransaksi(barang, jumlah))
        }

        hitungTotal()
    }

    /**
     * Menghapus item dari keranjang berdasarkan ID barang.
     */
    fun hapusItem(idBarang: String) {
        require(status == StatusTransaksi.PENDING) { "Tidak dapat menghapus item pada transaksi yang sudah ${status.name}." }
        val removed = _daftarItem.removeIf { it.barang.idBarang == idBarang }
        if (removed) {
            hitungTotal()
        }
    }

    /**
     * Menghitung subtotal, diskon member secara polimorfik, dan total akhir.
     */
    fun hitungTotal() {
        totalBelanja = _daftarItem.sumOf { it.subtotal }
        totalDiskon = 0.0

        // Penerapan polimorfisme diskon jika pelanggan adalah Member
        if (pelanggan is Member) {
            totalDiskon = pelanggan.hitungDiskon(totalBelanja)
        }

        totalAkhir = (totalBelanja - totalDiskon).coerceAtLeast(0.0)
    }

    /**
     * Menyelesaikan transaksi dengan validasi stok final dan pengurangan stok secara aman.
     */
    fun selesaikanTransaksi() {
        require(status == StatusTransaksi.PENDING) { "Transaksi ini sudah diproses sebelumnya dengan status: $status." }
        require(_daftarItem.isNotEmpty()) { "Gagal menyelesaikan transaksi: Keranjang belanja masih kosong." }

        // Validasi ulang menyeluruh sebelum stok dikurangi (mencegah race condition/perubahan stok mendadak)
        for (item in _daftarItem) {
            if (item.jumlahBeli > item.barang.stok) {
                throw IllegalStateException("Transaksi dibatalkan: Stok '${item.barang.nama}' berubah dan tidak mencukupi (Sisa: ${item.barang.stok}).")
            }
        }

        // Eksekusi pengurangan stok pada modul inventaris
        for (item in _daftarItem) {
            item.barang.kurangiStok(item.jumlahBeli)
        }

        status = StatusTransaksi.BERHASIL
    }

    /**
     * Membatalkan transaksi dan mengembalikan stok barang jika sebelumnya sudah berhasil.
     */
    fun batalkanTransaksi() {
        require(status != StatusTransaksi.DIBATALKAN) { "Transaksi sudah dibatalkan sebelumnya." }

        if (status == StatusTransaksi.BERHASIL) {
            // Kembalikan stok ke inventaris jika transaksi dibatalkan setelah sukses
            for (item in _daftarItem) {
                item.barang.tambahStok(item.jumlahBeli)
            }
        }

        status = StatusTransaksi.DIBATALKAN
    }
}