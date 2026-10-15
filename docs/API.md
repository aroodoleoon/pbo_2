# Kontrak Integrasi API POS (`docs/API.md`)
**Base URL**: `http://localhost:8080/api/v1`  
**Header Standar**:
- `Content-Type: application/json`
- `Authorization: Bearer <token>` (Untuk endpoint yang membutuhkan autentikasi)

---

## 1. Autentikasi (`/auth`)

### `POST /auth/login`
**Request**:
```json
{
  "username": "kasir1",
  "password": "password123"
}
```

**Response 200 OK**:
```json
{
  "accessToken": "POS-TOKEN-22222222-2222-2222-2222-222222222222-uuid",
  "user": {
    "id": "22222222-2222-2222-2222-222222222222",
    "name": "Dapin Kasir Pagi",
    "username": "kasir1",
    "role": "CASHIER",
    "active": true
  }
}
```

**Response 401 Unauthorized**:
```json
{
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Username atau kata sandi salah."
  }
}
```

---

## 2. Katalog & Barcode (`/products`)

### `GET /products/barcode/{barcode}`
Lookup produk via scanner barcode.

**Response 200 OK**:
```json
{
  "id": "brg-001",
  "barcode": "89999991001",
  "name": "Chitato Sapi Panggang 68g",
  "categoryId": "kat-001",
  "buyPrice": 8000,
  "sellPrice": 11500,
  "stock": 50,
  "minStock": 10,
  "active": true
}
```

**Response 404 Not Found**:
```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Barang dengan barcode '89999991001' tidak ditemukan."
  }
}
```

---

## 3. Checkout & Transaksi Terpadu (`/sales/checkout`)

Endpoint atomik ACID untuk transaksi penjualan.

**Request**:
```json
{
  "idempotencyKey": "chk-20261015-001-kasir1",
  "customerId": "plg-001",
  "items": [
    {
      "productId": "brg-001",
      "quantity": 2
    },
    {
      "productId": "brg-005",
      "quantity": 3
    }
  ],
  "payment": {
    "method": "CASH",
    "amountPaid": 50000
  }
}
```

**Response 201 Created**:
```json
{
  "transactionId": "trx-uuid-1234",
  "transactionNumber": "TRX-20261015-0001",
  "status": "COMPLETED",
  "subtotal": 33500,
  "discount": 0,
  "tax": 0,
  "total": 33500,
  "amountPaid": 50000,
  "change": 16500,
  "items": [
    {
      "productId": "brg-001",
      "productName": "Chitato Sapi Panggang 68g",
      "quantity": 2,
      "price": 11500,
      "subtotal": 23000
    },
    {
      "productId": "brg-005",
      "productName": "Indomie Goreng Spesial 85g",
      "quantity": 3,
      "price": 3500,
      "subtotal": 10500
    }
  ],
  "payment": {
    "method": "CASH",
    "amountPaid": 50000,
    "change": 16500,
    "status": "SUCCESS"
  },
  "createdAt": "2026-10-15T09:00:00Z"
}
```

**Response 409 Conflict (Stok Tidak Cukup / Out of Stock)**:
```json
{
  "error": {
    "code": "OUT_OF_STOCK",
    "message": "Stok tidak mencukupi untuk produk 'Chitato Sapi Panggang 68g'. Tersedia: 1 pcs, Diminta: 2 pcs."
  }
}
```

---

## 4. Kasir & Karyawan (`/employees`) *(Khusus Admin)*

- `GET /employees` — Daftar seluruh akun
- `POST /employees` — Daftarkan akun baru (password otomatis di-hash SHA-256 / BCrypt)
- `POST /employees/{id}/toggle` — Aktif / Nonaktifkan akun
- `DELETE /employees/{id}` — Hapus / Arsipkan akun
