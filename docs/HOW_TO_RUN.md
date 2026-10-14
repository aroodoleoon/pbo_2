# Panduan Cepat Menjalankan Sistem (How to Run)

Dokumen ini berisi panduan ringkas dan cepat untuk menjalankan **POS Academic Retail Management System**.

---

## ⚡ Cara Cepat 1: Docker Compose (Direkomendasikan)

Pastikan Docker Desktop aktif di komputer Anda.

```bash
# Jalankan seluruh service (Postgres, Backend, Client)
docker compose up --build -d

# Cek status kontainer
docker compose ps
```

### URL Akses:
* **Web Client**: [http://localhost:8000](http://localhost:8000)
* **Backend API**: [http://localhost:8080/api/v1](http://localhost:8080/api/v1)
* **PostgreSQL**: `localhost:5432` (`pos_db` / `pos_app` / `pos_password`)

### Stop Service:
```bash
docker compose down
```

---

## 💻 Cara Cepat 2: Menjalankan Lokal (Tanpa Docker)

Backend Ktor memiliki fitur **In-Memory H2 Fallback otomatis**, sehingga Anda tidak perlu repot setup database manual.

### Terminal 1: Backend Kotlin Ktor
```powershell
cd serverSide
.\gradlew.bat run
```
*(Tunggu hingga muncul log: `Responding at http://127.0.0.1:8080`)*

### Terminal 2: Frontend PHP Client
```powershell
# Jalankan dari root folder pbo_2
php -S 127.0.0.1:8000 -t client
```
*(Tunggu hingga muncul log: `Development Server started`)*

### Buka di Browser:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 Akun Demo Login

* **Admin**: `admin` / `admin123`
* **Kasir**: `kasir1` / `kasir123`

*(Tersedia tombol "Akun Cepat" pada form login untuk mengisi username dan password secara otomatis)*

---

## ⌨️ Shortcut Keyboard Kasir

* `F2`: Fokus ke scan barcode / pencarian
* `F9`: Buka modal pembayaran
* `Esc`: Batal transaksi / tutup modal

---

## 🧪 Menjalankan Automated Testing

```powershell
# 1. Unit Test Backend Kotlin
cd serverSide
.\gradlew.bat test

# 2. Unit Test Python Inventaris
cd "Modul 2/pos_inventaris"
python -m unittest discover tests

# 3. Postman CLI Automated Test
postman collection run docs/pos_system_postman_collection.json
```
