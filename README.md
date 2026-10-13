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
