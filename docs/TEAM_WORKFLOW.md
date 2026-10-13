# Workflow Tim

## Branch
- `main`: versi yang sudah teruji.
- `feat/nama-fitur`: pekerjaan tiap fitur.
- Hindari commit langsung ke `main`.

## Setiap tugas harus memiliki
- Penanggung jawab.
- Kriteria selesai.
- File/modul yang disentuh.
- Cara menjalankan dan menguji.
- Dependensi terhadap tugas lain.

## Definition of Done
- Kode dapat dibangun.
- Fitur sesuai kontrak.
- Validasi input tersedia.
- Test minimum lulus.
- Tidak ada secret di repository.
- Pull request ditinjau minimal satu anggota lain.
- README diperbarui jika cara menjalankan berubah.

## Komunikasi
- Check-in singkat setiap hari.
- Hambatan integrasi diangkat segera, jangan menunggu hari terakhir.
- Perubahan model atau API harus diumumkan ke anggota yang terdampak.
- Satu sumber kebenaran stok dan transaksi: PostgreSQL melalui backend.
