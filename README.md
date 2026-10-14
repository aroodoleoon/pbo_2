# POS Academic — Retail Management System (Tubes 2 PBO)

Sistem Kasir & Manajemen Ritel Terintegrasi berbasis **Object-Oriented Programming (OOP)** dengan arsitektur **Client-Server REST API**.

Proyek ini menggabungkan seluruh modul tugas besar PBO:
* **Modul 2 (Alex)**: Inventaris, Audit Pergerakan Stok, & Low-Stock Alerts.
* **Modul 3 (Dapin)**: Transaksi Kasir POS, Snapshot Harga, PPN 11%, & Diskon Bertingkat.
* **Modul 4 (Dhia)**: Karyawan, Autentikasi Role, & Program Member Loyalitas.
* **Modul 5 (Josua)**: Multi-Metode Pembayaran (Cash/QRIS/Debit), Database, & Laporan Penjualan.

---

## 🏛️ Arsitektur Sistem

```
┌────────────────────────────────────────────────────────┐
│                   POS Web Client                       │
│    PHP 8.2 OOP (MVC Architecture) • Google Stitch UI   │
│             http://127.0.0.1:8000                      │
└───────────────────────────┬────────────────────────────┘
                            │ HTTP / REST API (JSON)
                            ▼
┌────────────────────────────────────────────────────────┐
│                 POS Backend Server                     │
│         Kotlin 2.1 • Ktor 2.3 • OOP Domain Engine      │
│             http://127.0.0.1:8080                      │
└─────────────┬────────────────────────────┬─────────────┘
              │ JDBC                       │ Fallback Otomatis
              ▼                            ▼
┌──────────────────────────┐  ┌──────────────────────────┐
│  PostgreSQL 17 Database  │  │  In-Memory H2 Database   │
│       (Production)       │  │    (Lokal Dev Instan)    │
└──────────────────────────┘  └──────────────────────────┘
```

---

## 🚀 Panduan Menjalankan Aplikasi (How to Run)

Anda dapat menjalankan aplikasi ini melalui **dua metode**:

---

### METODE 1: Menggunakan Docker Compose *(Sangat Direkomendasikan)*

Metode ini otomatis mengompilasi dan menjalankan 3 kontainer terisolasi: **Database PostgreSQL**, **Backend Ktor API**, dan **Frontend PHP Client**.

#### 1. Prasyarat
* [Docker Desktop](https://www.docker.com/products/docker-desktop/) sudah terpasang dan aktif di komputer Anda.

#### 2. Jalankan Perintah
Buka terminal di root folder project (`pbo_2`), lalu ketik:
```bash
docker compose up --build -d
```

#### 3. Verifikasi Status Kontainer
```bash
docker compose ps
```
*Pastikan ketiga layanan (`pos-postgres`, `pos-backend`, `pos-client`) berstatus **Up (healthy)**.*

#### 4. Akses Aplikasi
* 🌐 **Web Client POS**: [**`http://localhost:8000`**](http://localhost:8000)
* 🔌 **REST API Backend**: [**`http://localhost:8080/api/v1`**](http://localhost:8080/api/v1)
* 🗄️ **PostgreSQL Database**: `localhost:5432` *(User: `pos_app`, Pass: `pos_password`, DB: `pos_db`)*

#### 5. Menghentikan Kontainer
```bash
docker compose down
```

---

### METODE 2: Menjalankan secara Lokal *(Development Mode tanpa Docker)*

Jika tidak menggunakan Docker, backend Ktor memiliki fitur **In-Memory H2 Fallback otomatis**, sehingga server langsung aktif dan melakukan *auto-seed* data tanpa perlu instalasi PostgreSQL terlebih dahulu.

#### 1. Prasyarat
* **Java / JDK 21 atau lebih baru** (`java -version`)
* **PHP 8.0 atau lebih baru** (`php -v`)

#### 2. Langkah 1: Jalankan Backend Server (Terminal 1)
Buka terminal pertama di root project:
```powershell
# Masuk ke folder backend dan jalankan
cd serverSide
.\gradlew.bat run
```
*Tunggu hingga muncul pesan: `[DATABASE READY]` dan `Responding at http://127.0.0.1:8080`.*

#### 3. Langkah 2: Jalankan Frontend PHP Client (Terminal 2)
Buka terminal kedua di root project:
```powershell
# Jalankan built-in web server PHP
php -S 127.0.0.1:8000 -t client
```
*Tunggu hingga muncul pesan: `Development Server (http://127.0.0.1:8000) started`.*

#### 4. Buka di Browser
Arahkan browser Anda ke:
👉 [**http://127.0.0.1:8000**](http://127.0.0.1:8000)

---

## 🔑 Kredensial Akun Pengujian (Demo Login)

Pada halaman login, tersedia tombol pintas **"Akun Cepat"** untuk login instan:

| Role | Username | Password | Hak Akses & Fitur |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` | Akses penuh: Master Produk, Stok Gudang, Laporan Keuangan, Pelanggan |
| **Kasir** | `kasir1` | `kasir123` | POS Kasir, Scan Barcode, Transaksi Pembayaran Tunai/QRIS, Cetak Struk |

---

## ⌨️ Pintasan Keyboard Kasir (Keyboard Shortcuts)

* <kbd>F2</kbd> : Fokus instan ke kolom scan barcode / input pencarian barang.
* <kbd>Enter</kbd> : Tambahkan produk barcode yang cocok ke antrian belanja.
* <kbd>F9</kbd> : Buka modal pembayaran instan (Cash / QRIS / Debit).
* <kbd>Esc</kbd> : Batalkan transaksi atau tutup popup struk belanja.

---

## 🖨️ Fitur Cetak Struk Belanja (Thermal Receipt)

Saat transaksi diselesaikan dan kasir menekan tombol **"Cetak Struk"**:
1. Sistem menggunakan *isolated iframe printing* sehingga **100% hanya kertas struk yang dicetak**.
2. Elemen navigasi web, sidebar, header, dan tombol-tombol antarmuka otomatis disembunyikan.
3. Format output kompatibel dengan printer kasir thermal roll ukuran **80mm & 58mm**.

---

## 🧪 Pengujian Otomatis (Testing)

### 1. Unit Test Backend Kotlin (Gradle)
```powershell
cd serverSide
.\gradlew.bat test
```
*Menguji seluruh aturan domain: enkapsulasi barang, pewarisan karyawan/admin/kasir, polimorfisme diskon member, dan kebenaran snapshot harga transaksi.*

### 2. Unit Test Modul Inventaris Python
```powershell
cd "Modul 2/pos_inventaris"
python -m unittest discover tests
```
*Menguji 14 skenario validasi stok fisik, batas minimum, dan pergerakan barang masuk/keluar.*

### 3. Pengujian REST API via Postman CLI
```powershell
postman collection run docs/pos_system_postman_collection.json
```
*Menguji seluruh alur: login token, tambah barang, transaksi kasir, pembayaran atomik, hingga laporan omzet (13 request, 25 assertion).*

---

## 📂 Struktur Direktori Proyek

```
pbo_2/
├── client/                        # Frontend Web PHP OOP (MVC Pattern)
│   ├── index.php                  # Front Controller & Routing
│   ├── Dockerfile                 # Kontainer PHP 8.2 Client
│   ├── src/                       # ApiClient, Models, Controllers, Session
│   ├── views/                     # Layout, POS, Inventory, Reports, Customers
│   └── stitch_designs/            # Desain UI Mentah dari Google Stitch MCP
├── serverSide/                    # Backend API Kotlin Ktor (Hexagonal/OOP)
│   ├── Dockerfile                 # Multi-stage Docker build Ktor
│   ├── build.gradle.kts           # Konfigurasi Gradle 9 & Kotlin 2.1
│   └── src/main/kotlin/com/pos/   # Domain, Service, Repository, DTO, Routes
├── database/                      # Skrip Database & Migrasi
│   ├── migrations/                # 001_initial_schema.sql (Tabel & Relasi)
│   └── seed.sql                   # Data Dummy Awal (Produk, User, Member)
├── docs/                          # Dokumentasi Lengkap & API Contract
│   ├── API_DOCUMENTATION.md       # Spesifikasi Endpoint REST API
│   ├── API_CONTRACT.md            # Ringkasan Kontrak Request/Response
│   ├── HOW_TO_RUN.md              # Panduan Cepat Menjalankan Sistem
│   └── pos_system_postman_collection.json # Automated API Test Collection
├── compose.yaml                   # Konfigurasi Docker Compose Resmi
├── docker-compose.yml             # Alias Docker Compose
└── README.md                      # Dokumentasi Utama Proyek
```

---

## 👥 Kontributor Tim (PBO Tubes 2)

* **Alex** — Modul 2: Inventaris & Audit Pergerakan Stok
* **Dapin** — Modul 3: Transaksi Kasir POS & Snapshot Keranjang
* **Dhia** — Modul 4: Karyawan, Autentikasi, & Program Loyalitas Member
* **Josua** — Modul 5: Sistem Pembayaran, Database, & Laporan Penjualan 