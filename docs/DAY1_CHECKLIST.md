# Day 1 Checklist

## Semua anggota
- [ ] Sepakati platform client, framework GUI, backend, database, dan versi toolchain.
- [ ] Sepakati scope MVP dan fitur yang ditunda.
- [ ] Review business rules.
- [ ] Review class diagram dan ERD.
- [ ] Review API contract.
- [ ] Sepakati penamaan, error format, uang, ID, waktu, dan role.
- [ ] Semua anggota bisa clone repository dan menjalankan Docker Compose.

## Leader
- [ ] Buat repository dan branch protection jika memungkinkan.
- [ ] Catat keputusan teknis di README/ADR.
- [ ] Pastikan API contract dipahami anggota client dan server.
- [ ] Buat issue untuk setiap deliverable.
- [ ] Jadwalkan integrasi harian.

## Anggota 2 — Inventaris
- [ ] Review model Barang, KategoriBarang, PergerakanStok.
- [ ] Daftar validasi CRUD dan stok.
- [ ] Sepakati endpoint dengan leader dan anggota 5.

## Anggota 3 — Transaksi
- [ ] Review Transaksi, DetailTransaksi, Pembayaran.
- [ ] Sepakati flow checkout dan aturan atomic SQL transaction.
- [ ] Daftar skenario gagal.

## Anggota 4 — Client GUI
- [ ] Buat wireframe login, dashboard, produk, checkout, riwayat.
- [ ] Sepakati DTO dan error response API.
- [ ] Siapkan mock data untuk development paralel.

## Anggota 5 — Data/infrastruktur
- [ ] Jalankan PostgreSQL (`postgres:17-alpine`).
- [ ] Verifikasi health check dan persistent volume.
- [ ] Review ERD dan migration strategy (termasuk tabel `audit_logs` dengan `JSONB`).
- [ ] Tentukan format audit log.

## Exit criteria
Day 1 selesai jika keputusan disetujui, repository tersedia, database hidup, diagram dan kontrak API tidak lagi berubah tanpa koordinasi.
