# Roadmap Pengembangan POS (Point of Sale) Tubes PBO 2
**Repository**: `aroodoleoon/pbo_2`  
**Metodologi**: Software Engineering Berbasis Tim & Kontrak Terpadu  
**Target Deadline**: 7 Hari Menuju Demo Dosen  

---

## 1. Arsitektur Sistem

- **Client**: Kotlin Compose Desktop (GUI Kasir Terminal Utama) & PHP Web Client (Manajemen Web & Backup).
- **Backend**: Kotlin Ktor REST API ter-kontainerisasi (Docker).
- **Database Utama**: PostgreSQL 17 (Single Source of Truth / ACID Transaksional).
- **NoSQL**: MongoDB 7 (Audit Logs, Jejak Perubahan Harga, Log Kasir Non-Kritis).

### Keputusan Penyimpanan Data
| Data | Penyimpanan | Alasan Rekayasa Perangkat Lunak |
| :--- | :--- | :--- |
| **Produk, Harga, Stok** | PostgreSQL | Membutuhkan relasi tabel, integritas referensial, dan konsistensi katalog. |
| **Transaksi, Detail, Pembayaran** | PostgreSQL | Membutuhkan transaksi atomik (ACID): update stok & pembayaran harus rollback jika salah satu gagal. |
| **Karyawan, Member, Poin** | PostgreSQL | Membutuhkan unique constraint dan integritas relasi. |
| **Audit Log & Log Aktivitas** | MongoDB | Format dokumen dinamis (JSON), append-only, tidak mengunci resource transaksi utama. |

---

## 2. Rincian Fase Pengembangan (7 Hari)

### Fase 1 · Stabilkan Fondasi (Hari 1–2)
- **Hari 1**: Audit repository, ERD final, pembekuan kontrak API di `docs/API_CONTRACT.md`, environment setup, dan Git branching workflow.
- **Hari 2**:
  - Eliminasi fallback diam-diam ke H2 pada mode Docker/Production.
  - Skema PostgreSQL berbasis migrasi berversi.
  - Autentikasi aman (BCrypt / Argon2id), RBAC role check.
  - Setup container MongoDB untuk audit logging.

### Fase 2 · Fitur Inti (Hari 3–4)
- **Hari 3**:
  - GUI Kasir Desktop (Compose Desktop): navigasi, ApiClient coroutine.
  - Pemisahan `id` (internal UUID), `sku` (kode inventaris), `barcode` (scanner).
  - Alur USB barcode scanner (`Scanner → Enter → GET /products/barcode/{barcode} → Tambah ke keranjang`).
  - Keranjang belanja (add, edit qty, remove, subtotal).
- **Hari 4**:
  - Alur checkout terpadu (`POST /api/v1/sales/checkout`).
  - Row locking / conditional stock update (anti stok minus, rollback terkoordinasi).
  - Idempotency key per checkout (anti double submit).
  - Pembayaran (Tunai hitung kembalian, QRIS simulasi) & cetak struk mandiri.

### Fase 3 · Integrasi Sistem (Hari 5)
- Penyelarasan payload request/response antara client dan backend Ktor.
- Uji coba menyeluruh seluruh container di Docker Compose (`postgres`, `mongodb`, `backend`, `client`).
- Penyelesaian mismatch data type atau field naming.

### Fase 4 · Pengujian & Demo (Hari 6–7)
- **Hari 6**: Eksekusi 12 Matriks Pengujian Kritis (Autentikasi, Otorisasi, Barcode 404, Stok Habis, Konkurensi 2 Kasir, Double Submit, Rollback DB, Pembayaran Kurang, Laporan Omzet, Recovery Data).
- **Hari 7**: Code freeze, uji fresh clone dari README, latihan skenario demo 8 langkah di hadapan penguji.

---

## 3. Pembagian Peran Anggota Tim

| Anggota | Kepemilikan Utama | Luaran Wajib |
| :--- | :--- | :--- |
| **Wildan** *(Lead)* | Arsitektur, Compose Desktop, Integrasi, Review | Client terintegrasi, Docker Compose stabil, Release Demo |
| **Alex** | Master Produk, Barcode Scanner, Inventaris | Katalog produk, index barcode scanner, audit stok |
| **Dapin** | Keranjang & Validasi Checkout | State keranjang, perhitungan subtotal/diskon, payload checkout |
| **Dhia** | Autentikasi, Otorisasi RBAC, Member Loyalty | Endpoint login, verifikasi token, role check, poin member |
| **Josua** | Modul Pembayaran, Struk, Agregasi Laporan | Pembayaran tunai/QRIS, template struk bersih, query laporan shift |

---

## 4. Matriks Pengujian 12 Kasus Uji

| No | Kasus Uji | Skenario | Ekspektasi |
| :---: | :--- | :--- | :--- |
| 1 | Autentikasi | Password salah | HTTP 401 Unauthorized, sesi tidak dibuat |
| 2 | Otorisasi | Kasir panggil API admin (`/employees`) | HTTP 403 Forbidden |
| 3 | Barcode | Barcode tidak ada di database | HTTP 404, keranjang belanja tidak berubah |
| 4 | Validasi Keranjang | Qty 0 atau negatif | Request ditolak validasi |
| 5 | Stok Kurang | Beli melebihi sisa stok fisik | Ditolak, stok tidak terpotong |
| 6 | Konkurensi | 2 kasir checkout sisa 1 barang bersamaan | 1 transaksi sukses, 1 transaksi ditolak out-of-stock |
| 7 | Idempotensi | Klik bayar 2x (double submit) | Hanya 1 transaksi dan 1 pembayaran yang terbentuk |
| 8 | Rollback DB | DB putus di tengah checkout | Seluruh perubahan rollback (tidak ada data menggantung) |
| 9 | Pembayaran | Uang tunai kurang dari total | Pembayaran ditolak, status pending |
| 10 | Laporan | Rekonsiliasi omzet vs transaksi sukses | Angka laporan 100% cocok dengan transaksi |
| 11 | NoSQL Log | Pengecekan event audit | Log tersimpan di MongoDB |
| 12 | Recovery | Restart container backend & postgres | Data tetap utuh (volume persisted) |

