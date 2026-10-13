# REST API Contract — draft

Base path: `/api/v1`
Format request/response: JSON
Waktu: ISO-8601 UTC
Nominal uang: integer rupiah
Error format yang disarankan:
```json
{
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "Stok tidak mencukupi",
    "requestId": "..."
  }
}
```

## Auth
- `POST /auth/login`
  - Request: `{ "username": "kasir1", "password": "..." }`
  - Response: `{ "accessToken": "...", "user": { "id": "...", "name": "...", "role": "CASHIER" } }`
- `POST /auth/logout` (opsional jika strategi token memerlukan invalidasi server)

## Products
- `GET /products?query=&page=1&pageSize=20`
- `POST /products` (Admin)
- `GET /products/{id}`
- `PATCH /products/{id}` (Admin)
- `GET /categories`
- `POST /categories` (Admin)

## Inventory
- `POST /inventory/receipts` — barang masuk
- `POST /inventory/adjustments` — penyesuaian stok dengan alasan
- `GET /inventory/movements?from=&to=&productId=`

## Customers
- `GET /customers?query=` (Admin & Kasir)
- `POST /customers` (Admin & Kasir — Kasir hanya boleh mendaftarkan pelanggan/member baru)
- `GET /customers/{id}` (Admin & Kasir)
- `PATCH /customers/{id}` (Khusus Admin — Kasir dilarang mengubah data pelanggan)

## Sales
- `POST /sales`
  - Request draft:
    `{ "customerId": null, "items": [{ "productId": "...", "quantity": 2 }] }`
  - Server membaca harga dan stok dari PostgreSQL (`stok >= 0`), mengecek syarat diskon/pajak, serta menyimpan keterangannya; client tidak boleh menentukan harga final.
- `GET /sales?from=&to=&page=1&pageSize=20`
- `GET /sales/{id}`

## Payments
- `POST /sales/{id}/payments`
  - Metode yang didukung (`method`): `"CASH" | "QRIS" | "TRANSFER" | "DEBIT"`
  - Request contoh: `{ "method": "CASH", "amountPaid": 50000 }`
  - Server menghitung total akhir (`subtotal - diskon + pajak`) dan kembalian (`0` untuk non-tunai), lalu menyimpan pembayaran serta mengurangi stok secara atomik.
- `GET /reports/sales?from=&to=`

## Ketentuan
- Semua endpoint kecuali login memerlukan autentikasi.
- Role/izin diperiksa di server.
- Gunakan status HTTP yang konsisten: 200/201, 400, 401, 403, 404, 409, 500.
- Idempotency key untuk proses pembayaran direkomendasikan agar retry tidak membuat transaksi ganda.
- Kontrak ini draft; implementasi final harus ditinjau bersama seluruh anggota sebelum coding.
