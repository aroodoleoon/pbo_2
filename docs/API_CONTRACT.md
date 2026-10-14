# REST API Contract — POS System MVP

> **Dokumentasi Lengkap dengan Request/Response JSON**: Silakan buka [**`docs/API_DOCUMENTATION.md`**](API_DOCUMENTATION.md).

## Ringkasan Spesifikasi
* **Base Path**: `/api/v1`
* **Format Request & Response**: JSON (`application/json`)
* **Format Timestamp**: ISO-8601 UTC
* **Format Uang**: Integer Rupiah (tanpa float/koma)

---

## Ringkasan Endpoint

| Modul | Method | Endpoint | Peran / Hak Akses | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **Auth** | `POST` | `/auth/login` | Publik | Login Admin / Kasir |
| | `POST` | `/auth/logout` | Terautentikasi | Keluar dari sesi |
| **Kategori** | `GET` | `/categories` | Admin & Kasir | Daftar kategori barang |
| | `POST` | `/categories` | Khusus Admin | Tambah kategori baru |
| **Produk** | `GET` | `/products` | Admin & Kasir | Ambil semua produk / pencarian |
| | `POST` | `/products` | Khusus Admin | Tambah produk baru dengan barcode |
| | `GET` | `/products/{id}` | Admin & Kasir | Detail spesifik produk |
| | `PATCH`| `/products/{id}` | Khusus Admin | Ubah harga atau info barang |
| **Inventaris** | `POST` | `/inventory/receipts` | Admin | Penerimaan barang masuk gudang |
| | `POST` | `/inventory/adjustments` | Admin | Penyesuaian barang rusak / hilang |
| | `GET` | `/inventory/movements` | Admin | Riwayat audit pergerakan stok |
| | `GET` | `/inventory/low-stock` | Admin & Kasir | Daftar barang dengan stok menipis |
| **Pelanggan** | `GET` | `/customers` | Admin & Kasir | Ambil pelanggan / cari member |
| | `POST` | `/customers` | Admin & Kasir | Daftarkan pelanggan / member baru |
| | `GET` | `/customers/{id}` | Admin & Kasir | Detail pelanggan |
| | `PATCH`| `/customers/{id}` | Khusus Admin | Ubah data pelanggan |
| **Penjualan** | `POST` | `/sales` | Kasir & Admin | Buat keranjang belanja & hitung total |
| | `POST` | `/sales/{id}/payments` | Kasir & Admin | Bayar tunai / QRIS / Transfer / Debit |
| | `POST` | `/payments/cash` | Kasir & Admin | Pembayaran tunai atomik instan |
| **Laporan** | `GET` | `/transactions` | Admin & Kasir | Riwayat seluruh transaksi |
| | `GET` | `/reports/sales` | Khusus Admin | Laporan penjualan harian/periode |

---

## Standar Kode Respons HTTP
* `200 OK`: Permintaan berhasil diproses.
* `201 Created`: Data baru berhasil disimpan.
* `400 Bad Request`: Input validasi gagal atau data tidak lengkap.
* `401 Unauthorized`: Kredensial login tidak cocok.
* `403 Forbidden`: Pengguna tidak memiliki izin role yang sesuai.
* `404 Not Found`: Entitas tidak ditemukan.
* `409 Conflict`: Konflik data (misal: stok tidak mencukupi).
* `500 Internal Error`: Kesalahan sistem atau database.
