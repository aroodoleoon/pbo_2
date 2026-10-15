# Dokumentasi Resmi REST API — POS System Backend

Dokumentasi lengkap antarmuka pemrograman aplikasi (REST API) untuk Sistem Kasir Point of Sale (POS) berbasis OOP Kotlin (Ktor) dan PostgreSQL.

---

## 1. Informasi Umum
* **Base URL**: `http://localhost:8080/api/v1`
* **Format Payload**: `application/json; charset=utf-8`
* **Format Waktu**: ISO-8601 UTC (contoh: `2026-10-14T02:24:23.838Z`)
* **Tipe Mata Uang**: Bilangan bulat (integer) Rupiah tanpa desimal (contoh: `15000` untuk Rp 15.000)
2
---

## 2. Standar Header & Autentikasi

| Header Key | Nilai Contoh | Keterangan |
| :--- | :--- | :--- |
| `Content-Type` | `application/json` | Wajib untuk semua request `POST` dan `PATCH` |
| `Authorization` | `Bearer POS-TOKEN-xxx` | Token akses yang didapatkan setelah login |
| `X-User-Id` | `11111111-1111-1111-1111-111111111111` | ID Karyawan yang sedang melakukan aksi (untuk audit/pencatatan) |
| `X-User-Role` | `ADMIN` atau `CASHIER` | Role pengguna aktif untuk verifikasi hak akses |

---

## 3. Format Respons Kesalahan (Error Response)

Semua endpoint yang mengalami kegagalan validasi atau kesalahan server akan mengembalikan format JSON standar:

```json
{
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "Stok barang 'Chitato' tidak mencukupi (tersedia: 5, diminta: 10).",
    "requestId": null
  }
}
```

### Daftar Kode Kesalahan (Error Codes):
* `VALIDATION_ERROR` (400 Bad Request): Input data tidak lengkap atau melanggar aturan bisnis.
* `BAD_REQUEST` (400 Bad Request): Format JSON atau parameter tidak valid.
* `UNAUTHORIZED` (401 Unauthorized): Username atau password salah.
* `FORBIDDEN` (403 Forbidden): Akun Kasir mencoba mengakses fitur khusus Admin.
* `PRODUCT_NOT_FOUND` / `NOT_FOUND` (404 Not Found): Data tidak ditemukan di database.
* `INSUFFICIENT_STOCK` (409 Conflict): Stok tidak mencukupi untuk penjualan.
* `INTERNAL_ERROR` (500 Internal Server Error): Terjadi kegagalan pada server database.

---

## 4. Daftar Endpoint API

### 4.1 Modul Autentikasi (`/auth`)

#### A. Login Karyawan
* **Endpoint**: `POST /auth/login`
* **Akses**: Publik
* **Request Body**:
```json
{
  "username": "admin",
  "password": "admin123"
}
```
* **Respons Sukses (`200 OK`)**:
```json
{
  "accessToken": "POS-TOKEN-11111111-1111-1111-1111-111111111111-9a7f...",
  "user": {
    "id": "11111111-1111-1111-1111-111111111111",
    "name": "Wildan Administrator",
    "username": "admin",
    "role": "ADMIN",
    "active": true
  }
}
```

#### B. Logout
* **Endpoint**: `POST /auth/logout`
* **Akses**: Pengguna yang sedang login
* **Respons Sukses (`200 OK`)**:
```json
{
  "message": "Berhasil keluar dari sesi."
}
```

---

### 4.2 Modul Master Barang & Kategori (`/categories`, `/products`)

#### A. Ambil Semua Kategori
* **Endpoint**: `GET /categories`
* **Akses**: Admin & Kasir
* **Respons Sukses (`200 OK`)**:
```json
[
  {
    "id": "33333333-3333-3333-3333-333333333331",
    "name": "Makanan Ringan"
  },
  {
    "id": "33333333-3333-3333-3333-333333333332",
    "name": "Minuman"
  }
]
```

#### B. Tambah Kategori Baru
* **Endpoint**: `POST /categories`
* **Akses**: Khusus Admin
* **Request Body**:
```json
{
  "name": "Alat Tulis Kantor"
}
```
* **Respons Sukses (`201 Created`)**:
```json
{
  "id": "c1a2b3c4-...",
  "name": "Alat Tulis Kantor"
}
```

#### C. Ambil Semua Produk (Katalog Barang)
* **Endpoint**: `GET /products`
* **Query Params**:
  * `query` *(opsional)*: Kata kunci nama atau kode barcode
  * `categoryId` *(opsional)*: Filter ID kategori
* **Akses**: Admin & Kasir
* **Respons Sukses (`200 OK`)**:
```json
[
  {
    "id": "44444444-4444-4444-4444-444444444441",
    "barcode": "89999991001",
    "name": "Chitato Sapi Panggang 68g",
    "categoryId": "33333333-3333-3333-3333-333333333331",
    "buyPrice": 8000,
    "sellPrice": 11500,
    "stock": 50,
    "minStock": 10,
    "active": true
  }
]
```

#### D. Detail Produk
* **Endpoint**: `GET /products/{id}`
* **Akses**: Admin & Kasir
* **Respons Sukses (`200 OK`)**: Objek produk tunggal.

#### E. Tambah Produk Baru
* **Endpoint**: `POST /products`
* **Akses**: Khusus Admin
* **Request Body**:
```json
{
  "barcode": "89999991005",
  "name": "Biskuit Roma Kelapa 300g",
  "categoryId": "33333333-3333-3333-3333-333333333331",
  "buyPrice": 7500,
  "sellPrice": 10000,
  "initialStock": 30,
  "minStock": 5
}
```
* **Respons Sukses (`201 Created`)**: Objek produk yang baru dibuat.

#### F. Ubah Produk / Ubah Harga
* **Endpoint**: `PATCH /products/{id}`
* **Akses**: Khusus Admin
* **Request Body**:
```json
{
  "sellPrice": 12000,
  "minStock": 8
}
```
* **Respons Sukses (`200 OK`)**: Objek produk terbaru.

---

### 4.3 Modul Inventaris & Stok (`/inventory`)

#### A. Penerimaan Barang Masuk (Stok Gudang)
* **Endpoint**: `POST /inventory/receipts`
* **Akses**: Admin / Petugas Gudang
* **Request Body**:
```json
{
  "productId": "44444444-4444-4444-4444-444444444441",
  "quantity": 20,
  "source": "PT Indofood Sukses Makmur",
  "notes": "Surat Jalan No. SJ-2026-001"
}
```
* **Respons Sukses (`201 Created`)**: Objek produk dengan stok yang telah bertambah.

#### B. Penyesuaian Barang Keluar (Rusak / Hilang / Lainnya)
* **Endpoint**: `POST /inventory/adjustments`
* **Akses**: Khusus Admin
* **Request Body**:
```json
{
  "productId": "44444444-4444-4444-4444-444444444441",
  "quantity": 2,
  "type": "OUT_DAMAGED",
  "reason": "Kemasan bocor saat penataan rak"
}
```
* **Respons Sukses (`200 OK`)**: Objek produk dengan stok yang telah berkurang.

#### C. Riwayat Pergerakan Stok
* **Endpoint**: `GET /inventory/movements`
* **Query Params**:
  * `productId` *(opsional)*: ID Barang
  * `from` *(opsional)*: Waktu awal (ISO-8601)
  * `to` *(opsional)*: Waktu akhir (ISO-8601)
* **Respons Sukses (`200 OK`)**:
```json
[
  {
    "id": "mov-001",
    "productId": "44444444-4444-4444-4444-444444444441",
    "type": "OUT_SALE",
    "quantity": 2,
    "reason": "Penjualan transaksi TRX-TEST-001",
    "employeeId": "22222222-2222-2222-2222-222222222222",
    "reference": "TRX-TEST-001",
    "timestamp": "2026-10-14T02:24:23.854Z"
  }
]
```

#### D. Daftar Barang Stok Menipis
* **Endpoint**: `GET /inventory/low-stock`
* **Akses**: Admin & Kasir
* **Respons Sukses (`200 OK`)**: Daftar produk yang `stok <= stokMinimum`.

---

### 4.4 Modul Pelanggan & Member (`/customers`)

#### A. Ambil Semua Pelanggan / Pencarian
* **Endpoint**: `GET /customers`
* **Query Params**:
  * `query` *(opsional)*: Nama, No. HP, atau No. Member
* **Akses**: Admin & Kasir
* **Respons Sukses (`200 OK`)**:
```json
[
  {
    "id": "55555555-5555-5555-5555-555555555551",
    "name": "Andi Santoso",
    "contact": "081234567890",
    "isMember": true,
    "memberNumber": "MBR-001",
    "points": 150,
    "tier": "REGULER"
  }
]
```

#### B. Daftarkan Pelanggan / Member Baru
* **Endpoint**: `POST /customers`
* **Akses**: Admin & Kasir
* **Request Body (Member Baru)**:
```json
{
  "name": "Dewi Sartika",
  "contact": "081345678901",
  "asMember": true
}
```
* **Respons Sukses (`201 Created`)**:
```json
{
  "id": "plg-new-uuid",
  "name": "Dewi Sartika",
  "contact": "081345678901",
  "isMember": true,
  "memberNumber": "MBR-83921",
  "points": 0,
  "tier": "REGULER"
}
```

#### C. Ubah Data Pelanggan
* **Endpoint**: `PATCH /customers/{id}`
* **Akses**: **Khusus Admin** *(Kasir dilarang, akan mendapat HTTP 403)*
* **Headers**: `X-User-Role: ADMIN`
* **Request Body**:
```json
{
  "name": "Dewi Sartika S.Kom",
  "contact": "081399998888"
}
```
* **Respons Sukses (`200 OK`)**: Data pelanggan yang telah diperbarui.

---

### 4.5 Modul Penjualan & Pembayaran (`/sales`, `/payments`)

#### A. Buat Keranjang Transaksi & Hitung Diskon/Pajak
* **Endpoint**: `POST /sales`
* **Akses**: Kasir & Admin
* **Headers**: `X-User-Id: 22222222-2222-2222-2222-222222222222`
* **Request Body**:
```json
{
  "customerId": "55555555-5555-5555-5555-555555555551",
  "items": [
    {
      "productId": "44444444-4444-4444-4444-444444444441",
      "quantity": 2
    }
  ],
  "discountAmount": 0
}
```
* **Respons Sukses (`201 Created`)**:
```json
{
  "id": "trx-uuid-001",
  "transactionNumber": "TRX-1728876543210",
  "cashierId": "22222222-2222-2222-2222-222222222222",
  "customerId": "55555555-5555-5555-5555-555555555551",
  "subtotal": 23000,
  "discount": 1150,
  "tax": 2403,
  "total": 24253,
  "discountDescription": "Diskon Member REGULER (5%)",
  "status": "PENDING",
  "createdAt": "2026-10-14T02:30:00Z",
  "items": [
    {
      "id": "det-001",
      "productId": "44444444-4444-4444-4444-444444444441",
      "productName": "Chitato Sapi Panggang 68g",
      "quantity": 2,
      "unitPrice": 11500,
      "subtotal": 23000
    }
  ]
}
```

#### B. Pembayaran Kasir Multi-Metode (CASH, QRIS, TRANSFER, DEBIT)
* **Endpoint**: `POST /sales/{id}/payments`
* **Akses**: Kasir & Admin
* **Request Body (Pembayaran CASH)**:
```json
{
  "method": "CASH",
  "amountPaid": 30000
}
```
* **Request Body (Pembayaran QRIS / Non-Tunai)**:
```json
{
  "method": "QRIS",
  "amountPaid": 24253
}
```
* **Respons Sukses (`201 Created`)**:
```json
{
  "paymentId": "PAY-8f2a1...",
  "transactionId": "trx-uuid-001",
  "method": "CASH",
  "subtotal": 23000,
  "discountAmount": 1150,
  "total": 24253,
  "amountPaid": 30000,
  "changeAmount": 5747,
  "status": "SUCCESS",
  "timestamp": "2026-10-14T02:31:00Z"
}
```

#### C. Pembayaran Kasir Tunai Instan (Format Modul 5 Josua)
* **Endpoint**: `POST /payments/cash` atau `POST /api/payments/cash`
* **Akses**: Kasir & Admin
* **Request Body**:
```json
{
  "sale": {
    "transactionId": "TRX-20261014-001",
    "cashierId": "22222222-2222-2222-2222-222222222222",
    "customerId": null,
    "items": [
      {
        "itemId": "44444444-4444-4444-4444-444444444441",
        "quantity": 2
      }
    ],
    "discountAmount": 0
  },
  "amountPaid": 30000
}
```
* **Respons Sukses (`201 Created`)**: Objek `PaymentResponse` lengkap dengan nominal kembalian.

---

### 4.6 Modul Riwayat & Laporan (`/transactions`, `/reports`)

#### A. Riwayat Transaksi Penjualan
* **Endpoint**: `GET /transactions`
* **Query Params**:
  * `limit` *(opsional, default: 100)*: Jumlah transaksi yang ditampilkan
* **Akses**: Admin & Kasir
* **Respons Sukses (`200 OK`)**:
```json
[
  {
    "transactionId": "TRX-TEST-001",
    "transactionDate": "2026-10-14T02:24:23.838Z",
    "cashierId": "22222222-2222-2222-2222-222222222222",
    "customerId": "55555555-5555-5555-5555-555555555551",
    "subtotal": 27000,
    "discountAmount": 0,
    "total": 27000,
    "paymentMethod": "CASH",
    "amountPaid": 30000,
    "changeAmount": 3000,
    "paymentStatus": "SUCCESS"
  }
]
```

#### B. Rekap Laporan Penjualan Harian
* **Endpoint**: `GET /reports/sales`
* **Query Params**:
  * `from` *(wajib)*: Tanggal awal (format: `YYYY-MM-DD`)
  * `to` *(wajib)*: Tanggal akhir (format: `YYYY-MM-DD`)
* **Akses**: Khusus Admin
* **Respons Sukses (`200 OK`)**:
```json
[
  {
    "salesDate": "2026-10-14",
    "transactionCount": 1,
    "grossSales": 27000,
    "discountTotal": 0,
    "netSales": 27000
  }
]
```

