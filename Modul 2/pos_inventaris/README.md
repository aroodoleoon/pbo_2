# Modul Inventaris Barang — Proyek POS OOP (Anggota 2)

Python 3.9+ dan SQLite (bawaan Python, tanpa install tambahan).

## Cara jalan
    python main_inventaris.py          # menu CLI (membuat pos.db)
    python -m unittest -v              # jalankan 14 pengujian

## Struktur
| File | Isi |
|---|---|
| inventaris/barang.py | Kelas `Barang` (encapsulation: stok & harga lewat metode) |
| inventaris/kategori_barang.py | Kelas `KategoriBarang` |
| inventaris/pergerakan_stok.py | `PergerakanStok` + enum `JenisPergerakan` |
| inventaris/repository.py | Akses SQLite + transaksi atomik (`with repo.transaksi():`) |
| inventaris/service.py | `InventarisService`: SATU sumber logika stok |
| inventaris/exceptions.py | Error khusus modul |
| main_inventaris.py | Menu CLI untuk uji mandiri |
| test_inventaris.py | Unit test |

## Kontrak untuk modul lain (WAJIB disepakati dengan Anggota 1, 3, 5)
    svc.cek_stok_tersedia(id_barang, jumlah) -> bool           # Anggota 3, validasi saat tambah item
    svc.ambil_harga_jual(id_barang) -> float                   # Anggota 3, snapshot hargaSaatTransaksi
    svc.kurangi_stok_penjualan([(id, jml), ...], id_karyawan, id_transaksi)   # setelah bayar berhasil, atomik
    svc.kembalikan_stok_pembatalan([(id, jml), ...], id_karyawan, id_transaksi)
    repo.transaksi()   # context manager; bisa bersarang, dipakai Anggota 5 agar
                       # simpan transaksi + pembayaran + kurangi stok = 1 transaksi DB

## Progres per hari
**Hari 1 — Fondasi & desain**: baca kebutuhan, tentukan atribut `Barang`/`KategoriBarang`/`PergerakanStok`,
skema tabel `kategori_barang`, `barang`, `pergerakan_stok`, daftar kontrak antarmodul (di atas).
Output: class diagram & skema tabel untuk disepakati tim.

**Hari 2 — Kelas domain**: `Barang` (validasi, tambah/kurangi stok, ubah harga, aktif/nonaktif,
stok menipis), `KategoriBarang`, `PergerakanStok`, exceptions.

**Hari 3 — Penyimpanan**: `InventarisRepository` (SQLite), transaksi atomik, CRUD barang & kategori,
pencarian, query riwayat.

**Hari 4 — Fitur inti**: `InventarisService`: tambah/lihat/ubah/nonaktifkan barang, barang masuk,
barang keluar (rusak/hilang/lain, alasan wajib), setiap perubahan stok otomatis tercatat di riwayat.

**Hari 5 — Kontrak integrasi**: `kurangi_stok_penjualan` (atomik multi-item), `kembalikan_stok_pembatalan`,
`cek_stok_tersedia`, `ambil_harga_jual`, daftar stok menipis.

**Hari 6 — Pengujian & CLI**: 14 unit test (stok cukup/kurang, rollback multi-item, barang rusak,
pembatalan, duplikat, validasi), menu CLI `main_inventaris.py`.

**Hari 7 — Integrasi & dokumentasi**: sesuaikan nama metode dengan hasil kesepakatan tim, sambungkan
`id_karyawan` ke sesi login Anggota 4, rapikan README, siapkan bahan presentasi.

## Catatan
- Penerapan OOP: *encapsulation* (stok/harga privat), *abstraction* (service menyembunyikan SQL),
  pemisahan tanggung jawab (domain / repository / service).
- Pembatasan hak akses ubah harga dilakukan di lapisan menu/Autentikasi milik Anggota 4.
- Nama memakai gaya Python `snake_case` (mis. `hargaJual` -> `harga_jual`); sesuaikan bila tim sepakat lain.
