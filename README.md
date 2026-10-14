# POS OOP Kotlin — Day 1 Starter

Starter fondasi untuk MVP POS client-server selama 7 hari.

## Keputusan awal
- Client GUI: Kotlin + Compose Multiplatform Desktop
- Backend API: Kotlin + Ktor
- API: REST + JSON
- Database: PostgreSQL (`postgres:17-alpine`) sebagai sumber data utama sekaligus penyimpanan `audit_logs` berbasis `JSONB` (ringan, cukup 1 container)
- Deployment backend + database: Docker Compose
- Repository: satu monorepo

> Ini starter fondasi, bukan aplikasi POS yang sudah selesai. Dependensi Gradle dan implementasi server/client perlu dilengkapi serta diuji di lingkungan tim.

## Tujuan Day 1
1. Sepakati scope dan aturan bisnis.
2. Sepakati class diagram, ERD, dan kontrak API.
3. Buat repository dan standar kerja.
4. Jalankan PostgreSQL via Docker.
5. Siapkan kerangka client/server.

## Struktur
```text
pos-system/
├── client/
├── server/
├── shared/
├── database/
│   ├── migrations/
│   └── seed.sql
├── docs/
│   ├── SCOPE.md
│   ├── BUSINESS_RULES.md
│   ├── CLASS_DIAGRAM.md
│   ├── ERD.md
│   ├── API_CONTRACT.md
│   ├── TEAM_WORKFLOW.md
│   └── DAY1_CHECKLIST.md
├── compose.yaml
├── .env.example
└── .gitignore
```

## Menjalankan database
1. Salin `.env.example` menjadi `.env`.
2. Ganti password contoh.
3. Jalankan:
   ```bash
   docker compose up -d postgres
   docker compose ps
   ```
4. Cek log:
   ```bash
   docker compose logs postgres
   ```

Jangan commit `.env` ke Git. Port database hanya untuk development lokal.

## Definisi selesai Day 1
- [ ] Semua anggota setuju dengan scope MVP.
- [ ] Semua anggota setuju dengan diagram dan nama model.
- [ ] Kontrak API disetujui.
- [ ] Repository GitHub sudah dibuat.
- [ ] Semua anggota berhasil clone repository.
- [ ] Docker Compose menjalankan PostgreSQL.
- [ ] Keputusan teknis dan pembagian tugas tercatat.


# POS OOP Kotlin — Day 2 Development

Pengembangan modul utama untuk MVP POS client-server selama 7 hari.

## Progres Day 2

- Struktur repository GitHub sudah dibuat dan seluruh anggota kelompok sudah bergabung.
- Modul dan class Kotlin sudah mulai dibuat berdasarkan pembagian tugas masing-masing anggota.
- Logic utama program sudah dibuat, dengan fokus pada penyelesaian fungsi inti setiap modul.
- Tahap pengembangan berikutnya berfokus pada perapihan kode, penyesuaian struktur antarkelas, dan integrasi seluruh modul.

> Proyek masih dalam tahap pengembangan. Logic utama sudah tersedia, tetapi program secara keseluruhan masih memerlukan perapihan, integrasi, dan pengujian untuk memastikan seluruh modul dapat berjalan bersama.

## Pencapaian Day 2

1. Membuat dan mengembangkan class Kotlin sesuai pembagian tugas setiap anggota.
2. Menyiapkan repository GitHub sebagai tempat penyimpanan kode dan kolaborasi kelompok.
3. Menggabungkan seluruh anggota kelompok ke dalam repository agar pengembangan dapat dilakukan secara terkoordinasi.
4. Menyelesaikan logic utama pada modul yang sedang dikembangkan.
5. Menyiapkan tahap perapihan kode dan integrasi antarmodul.

## Struktur Repository

```text
pbo_2/
├── Modul 2/
├── Modul 3/
├── Modul 5/
├── client/
├── database/
├── docs/
├── serverSide/
├── .env.example
├── .gitignore
├── README.md
└── compose.yaml
```

Struktur repository mengikuti pengembangan sistem POS berbasis Kotlin dengan pendekatan monorepo. Folder modul digunakan untuk mengorganisasi pengerjaan berdasarkan pembagian tugas, sedangkan folder client, server, database, dan docs mendukung pengembangan serta integrasi sistem.

## Fokus Pengembangan

- **Modul Kotlin:** Melengkapi dan merapikan class serta logic utama sesuai tanggung jawab masing-masing anggota.
- **Integrasi antarmodul:** Menyamakan nama class, atribut, parameter, fungsi, dan struktur data agar modul dapat saling berkomunikasi.
- **Backend dan database:** Menyesuaikan implementasi server serta penyimpanan data dengan kebutuhan sistem POS.
- **Pengujian:** Memastikan logic utama berjalan dan tidak menimbulkan konflik ketika modul digabungkan.
- **Kolaborasi GitHub:** Menjaga struktur kode dan perubahan setiap anggota agar proses integrasi lebih terkontrol.

## Kendala dan Catatan

- Beberapa bagian program masih memerlukan perapihan sebelum integrasi akhir.
- Struktur data dan kontrak antarmodul perlu dipastikan konsisten agar tidak terjadi konflik saat penggabungan.
- Program secara keseluruhan masih perlu diuji setelah seluruh modul terintegrasi.

## Definisi Selesai Day 2

- [x] Repository GitHub sudah dibuat.
- [x] Seluruh anggota kelompok sudah bergabung ke repository.
- [x] Class Kotlin untuk modul yang dikerjakan sudah mulai dibuat.
- [x] Logic utama program sudah dibuat.
- [ ] Kode setiap modul sudah dirapikan.
- [ ] Kontrak dan struktur data antarmodul sudah dipastikan konsisten.
- [ ] Seluruh modul berhasil diintegrasikan.
- [ ] Pengujian integrasi berhasil dijalankan.

## Target Day 3

1. Merapikan struktur dan implementasi class Kotlin.
2. Memeriksa kesesuaian kontrak antarmodul.
3. Mulai mengintegrasikan modul ke dalam sistem utama.
4. Melakukan pengujian terhadap fungsi inti dan alur kerja POS.
5. Memperbaiki error yang ditemukan selama proses integrasi.