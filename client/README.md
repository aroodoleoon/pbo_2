# POS Academic — PHP Client Application

Aplikasi Frontend POS (Point of Sale) berbasis **PHP Object-Oriented Programming (OOP)** yang mengintegrasikan seluruh screen desain Google Stitch dengan REST API Backend Kotlin Ktor.

---

## 🏛️ Arsitektur OOP Client

Struktur kode dibangun menggunakan pola arsitektur **MVC (Model-View-Controller)** murni dengan standar OOP:

```
client/
├── index.php                     # Front Controller & Request Router
├── README.md
├── src/                          # OOP Core Classes (Namespace: PosClient)
│   ├── Config.php                # Konfigurasi Endpoint API & PPN 11%
│   ├── Session.php               # OOP Session & Flash Messages Manager
│   ├── ApiClient.php             # OOP HTTP Client (cURL, Token Auth, Error Handling)
│   ├── Models/                   # Domain Models & DTOs
│   │   ├── Product.php           # Enkapsulasi data produk, harga jual, stok alert
│   │   ├── Customer.php          # Enkapsulasi member, tier diskon (5% s.d. 12%), poin
│   │   └── Cart.php              # Keranjang belanja, perhitungan PPN, subtotal, diskon
│   └── Controllers/              # Controller Penanganan Bisnis & Routing
│       ├── AuthController.php    # Login & Logout sesi kasir/admin
│       ├── PosController.php     # Terminal Kasir, scan barcode, checkout tunai/QRIS
│       ├── InventoryController.php# Modul Alex: Restok masuk & Penyesuaian barang rusak
│       ├── ReportController.php   # Modul Josua: Analitik omzet & Ekspor CSV
│       └── CustomerController.php # Modul Dhia: Registrasi member & Tier diskon
├── views/                        # Template Tampilan (Tailwind CSS CDN + Material Symbols)
│   ├── layout/
│   │   ├── head.php              # Header HTML, Tailwind Stitch Theme, Fonts
│   │   ├── sidebar.php           # Navigasi sidebar responsif
│   │   ├── header.php            # Status koneksi API, jam, toast notifikasi
│   │   └── foot.php              # Footer penutup
│   ├── login.php                 # Halaman login kasir & admin
│   ├── pos.php                   # Layar POS Kasir & Modal Pembayaran (Stitch Design)
│   ├── inventory.php             # Layar Manajemen Stok & Restok Gudang (Stitch Design)
│   ├── reports.php               # Layar Laporan Penjualan & Ekspor CSV (Stitch Design)
│   └── customers.php             # Layar Data Pelanggan & Member Loyalitas
└── stitch_designs/               # File mentah HTML hasil download dari Google Stitch MCP
```

---

## 🚀 Cara Menjalankan

### 1. Jalankan Backend Ktor (Terminal 1)
```powershell
cd d:\polman\oop\tubes_2\pbo_2\serverSide
.\gradlew.bat run
```
*Backend API akan berjalan di `http://127.0.0.1:8080`.*

### 2. Jalankan Client PHP (Terminal 2)
```powershell
cd d:\polman\oop\tubes_2\pbo_2
php -S 127.0.0.1:8000 -t client
```
*Buka browser di `http://127.0.0.1:8000`.*

---

## 🔑 Kredensial Login
* **Admin**: `admin` / `admin123` (Hak akses penuh: Katalog, Inventaris, Laporan, Pelanggan)
* **Kasir**: `kasir1` / `kasir123` (Hak akses: POS Kasir, Pencarian Barang, Transaksi)

---

## ⌨️ Shortcut Keyboard Kasir
* `F2`: Fokus ke kolom scan barcode / cari barang
* `F9`: Buka modal pembayaran instan
* `Esc`: Batalkan modal / reset pencarian

