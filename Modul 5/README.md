# POS Kotlin — Modul Anggota 5

Starter integrasi mengikuti monorepo repo `pbo_2`: `shared/`, `server/`, `database/`; backend Ktor + PostgreSQL.

## File
- `PaymentModels.kt`: kontrak data lintas modul.
- `StockGateway.kt`: kontrak yang diimplementasikan Anggota 2; tidak membuat logika stok kedua.
- `PosRepository.kt`: simpan transaksi/detail/pembayaran, riwayat, laporan.
- `PaymentService.kt`: pembayaran tunai dan transaksi atomik.
- `PaymentRoutes.kt`: contoh endpoint Ktor.
- `V001__payment_and_sales.sql`: tabel yang dimiliki modul pembayaran.
- `PaymentRulesTest.kt`: contoh tes aturan bisnis.

## Kontrak integrasi
1. Anggota 3 mengirim `SaleRequest` berisi ID transaksi, kasir, pelanggan opsional, daftar barang, dan diskon.
2. Anggota 2 mengimplementasikan `StockGateway.prepareSale()` untuk membaca harga/stok aktual dan `applySale()` untuk mengurangi stok + mencatat pergerakan stok. Keduanya wajib memakai `Connection` yang diberikan; jangan membuka koneksi baru.
3. Tabel `barang` diharapkan memiliki `id_barang`, `nama`, `harga_jual`, `stok`, dan status aktif. Cocokkan nama/tipe kolom dengan Anggota 2 sebelum integrasi.
4. ID kasir/pelanggan perlu disamakan dengan modul Anggota 4.
5. Jangan membuat stok lokal/in-memory di modul ini.

## Dependencies
Kode mengasumsikan Ktor server, Kotlin serialization, PostgreSQL JDBC driver dan `DataSource` sudah disiapkan. Tambahkan `ktor-server-core`, `ktor-server-content-negotiation`, `ktor-serialization-kotlinx-json`, `kotlinx-serialization-json`, dan `org.postgresql:postgresql` sesuai versi Gradle repo.

Konfigurasi database melalui `JDBC_DATABASE_URL`, `DB_USER`, `DB_PASSWORD`; jangan commit password atau `.env`.

## Endpoint
- `POST /api/payments/cash`
- `GET /api/transactions?limit=100`
- `GET /api/reports/sales?from=2026-10-01&to=2026-10-31`

Migrasi membuat tabel `transaksi`, `detail_transaksi`, `pembayaran`. Jalankan setelah disepakati dengan anggota lain.
