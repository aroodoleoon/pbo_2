"""Menu CLI untuk menguji modul inventaris secara mandiri.
Nanti dipanggil/diintegrasikan oleh menu utama milik Team Leader (Anggota 1)."""
from inventaris import (InventarisRepository, InventarisService, InventarisError,
                        JenisPergerakan)

ID_PETUGAS = "ADM001"   # sementara; nanti diambil dari sesi login (Anggota 4)


def input_int(teks):
    try:
        return int(input(teks))
    except ValueError:
        raise InventarisError("Input harus berupa angka bulat.")


def input_harga(teks):
    try:
        return float(input(teks))
    except ValueError:
        raise InventarisError("Input harus berupa angka.")


def tampil(barang_list):
    if not barang_list:
        print("(kosong)"); return
    print(f"{'ID':<8}{'Nama':<24}{'Kat':<5}{'Beli':>10}{'Jual':>10}{'Stok':>6}{'Min':>5}  Status")
    for b in barang_list:
        flag = " [MENIPIS]" if b.stok_menipis() else ""
        st = "aktif" if b.aktif else "nonaktif"
        print(f"{b.id_barang:<8}{b.nama:<24}{str(b.id_kategori or '-'):<5}"
              f"{b.harga_beli:>10,.0f}{b.harga_jual:>10,.0f}{b.stok:>6}{b.stok_minimum:>5}  {st}{flag}")


def menu(svc):
    while True:
        print("""
=== MODUL INVENTARIS ===
1. Tambah kategori        7. Barang keluar (rusak/hilang/lain)
2. Daftar kategori        8. Riwayat pergerakan stok
3. Tambah barang          9. Stok menipis
4. Lihat/cari barang     10. Nonaktifkan / aktifkan barang
5. Ubah barang/harga      0. Keluar
6. Barang masuk""")
        p = input("Pilih: ").strip()
        try:
            if p == "1":
                k = svc.tambah_kategori(input("Nama kategori: "))
                print(f"Kategori dibuat dengan ID {k.id_kategori}.")
            elif p == "2":
                for k in svc.daftar_kategori():
                    print(k.id_kategori, k.nama)
            elif p == "3":
                svc.tambah_barang(input("Kode: "), input("Nama: "),
                                  input_int("ID kategori: "), input_harga("Harga beli: "),
                                  input_harga("Harga jual: "), input_int("Stok awal: "),
                                  input_int("Stok minimum: "), ID_PETUGAS)
                print("Barang ditambahkan.")
            elif p == "4":
                tampil(svc.cari_barang(input("Kata kunci (kosong=semua): "), termasuk_nonaktif=True))
            elif p == "5":
                kode = input("Kode barang: ")
                if input("Ubah harga? (y/n): ").lower() == "y":
                    svc.ubah_harga(kode, input_harga("Harga beli: "), input_harga("Harga jual: "))
                nama = input("Nama baru (kosong=tetap): ").strip() or None
                smin = input("Stok minimum baru (kosong=tetap): ").strip()
                svc.ubah_barang(kode, nama=nama, stok_minimum=int(smin) if smin else None)
                print("Data diperbarui.")
            elif p == "6":
                svc.barang_masuk(input("Kode: "), input_int("Jumlah: "),
                                 input("Sumber/supplier: "), ID_PETUGAS)
                print("Stok bertambah & tercatat.")
            elif p == "7":
                jenis = {"1": "KELUAR_RUSAK", "2": "KELUAR_HILANG", "3": "KELUAR_LAIN"}.get(
                    input("Jenis (1=rusak, 2=hilang, 3=lain): "))
                if not jenis:
                    raise InventarisError("Jenis tidak valid.")
                svc.barang_keluar(input("Kode: "), input_int("Jumlah: "), jenis,
                                  input("Alasan: "), ID_PETUGAS)
                print("Stok berkurang & tercatat.")
            elif p == "8":
                kode = input("Kode barang (kosong=semua): ").strip() or None
                for r in svc.riwayat_pergerakan(kode):
                    print(r.tanggal, r.id_barang, r.jenis.value, r.jumlah, "|", r.alasan, "|", r.id_karyawan)
            elif p == "9":
                tampil(svc.daftar_stok_menipis())
            elif p == "10":
                kode = input("Kode: ")
                if input("1=nonaktifkan, 2=aktifkan: ") == "1":
                    svc.nonaktifkan_barang(kode)
                else:
                    svc.aktifkan_barang(kode)
                print("Status diperbarui.")
            elif p == "0":
                break
        except InventarisError as e:
            print("GAGAL:", e)


if __name__ == "__main__":
    repo = InventarisRepository("pos.db")
    menu(InventarisService(repo))
    repo.tutup()
