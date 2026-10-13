# Aturan Bisnis

1. ID barang, karyawan, pelanggan/member, dan transaksi harus unik.
2. Kode barang menggunakan nomor barcode yang tertera pada kemasan tiap barang dan bersifat unik.
3. Harga dan jumlah harus bernilai valid; jumlah penjualan harus lebih dari nol.
4. Stok barang tidak boleh bernilai negatif (`stok >= 0`); penjualan tidak boleh melebihi stok tersedia.
5. Harga pada detail transaksi adalah snapshot saat transaksi dibuat; perubahan harga barang tidak mengubah transaksi lama.
6. Pembelian boleh dilakukan dengan atau tanpa member.
7. Kasir hanya diperbolehkan mencari dan mendaftarkan pelanggan/member baru; perubahan atau penghapusan data pelanggan khusus dilakukan oleh Admin.
8. Diskon dan pajak dihitung berdasarkan syarat yang berlaku (misalnya diskon khusus Member dan/atau syarat minimal belanja, serta PPN): `total_akhir = (subtotal - diskon) + pajak`.
9. Metode pembayaran dikelompokkan menjadi `CASH`, `QRIS`, `TRANSFER`, dan `DEBIT`:
   - Untuk `CASH`: nominal bayar harus $\ge$ total akhir transaksi, dan `kembalian = nominal_bayar - total_akhir`.
   - Untuk non-tunai (`QRIS`, `TRANSFER`, `DEBIT`): dicatat sebagai pengelompokan metode pembayaran dengan `nominal_bayar = total_akhir` dan `kembalian = 0`.
10. Transaksi baru dianggap selesai setelah pembayaran tervalidasi dan penyimpanan berhasil.
11. Penyimpanan transaksi, detail transaksi, pembayaran, dan pengurangan stok dilakukan dalam satu transaksi SQL.
12. Jika salah satu operasi SQL gagal, seluruh perubahan transaksi harus rollback.
13. Perubahan stok harus membuat catatan pergerakan stok beserta jenis, jumlah, alasan, waktu, dan petugas.
14. Transaksi yang sudah selesai tidak dihapus diam-diam. Pembatalan/retur ditangani sebagai alur tersendiri bila masuk scope.
15. Password tidak pernah disimpan dalam bentuk plaintext.
16. Otorisasi diperiksa di server, bukan hanya dengan menyembunyikan tombol di GUI.
17. PostgreSQL adalah satu-satunya database utama untuk barang, stok, transaksi, pembayaran, serta tabel `audit_logs` (menggunakan kolom `JSONB` untuk metadata event yang fleksibel dan ringan).
