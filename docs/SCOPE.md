# Scope MVP POS — versi Day 1

## Tujuan
Membuat aplikasi Point of Sale untuk demo akademik dengan Kotlin GUI, backend API, dan PostgreSQL (termasuk `JSONB` untuk audit log agar ringan).

## Fitur wajib
1. Login untuk Admin dan Kasir.
2. Manajemen barang (menggunakan kode barcode kemasan) dan kategori.
3. Pencatatan barang masuk/keluar serta pergerakan stok (stok tidak boleh negatif).
4. Transaksi penjualan dengan keranjang serta perhitungan diskon bersyarat & pajak.
5. Pelanggan member dan transaksi non-member (Kasir hanya bisa daftar baru, ubah data khusus Admin).
6. Pembayaran dengan pengelompokan metode (`CASH`, `QRIS`, `TRANSFER`, `DEBIT`) dan perhitungan kembalian.
7. Riwayat transaksi.
8. Laporan penjualan dasar.
9. Audit log aktivitas ke tabel `audit_logs` (`JSONB`) di PostgreSQL.
10. Docker Compose untuk backend dan database.

## Ditunda
- Integrasi payment gateway eksternal otomatis (cukup pengelompokan metode pembayaran).
- Printer thermal.
- Multi-gudang.
- Dashboard analitik lanjutan.

## Kriteria demo MVP
- Admin bisa login dan mengelola barang serta mengubah data pelanggan.
- Kasir bisa login, mendaftarkan pelanggan baru, dan melakukan transaksi.
- Penjualan tidak bisa melebihi stok tersedia (stok tidak boleh negatif).
- Transaksi menyimpan snapshot harga jual, syarat diskon/pajak, dan total akhir saat checkout.
- Pembayaran kurang ditolak.
- Transaksi berhasil mengurangi stok dan menyimpan detailnya.
- Transaksi non-member tetap bisa dilakukan.
- Riwayat transaksi bisa ditampilkan.
- Aktivitas penting dicatat di tabel `audit_logs` PostgreSQL.
