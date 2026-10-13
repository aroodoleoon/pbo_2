import unittest
from inventaris import *


class TestInventaris(unittest.TestCase):
    def setUp(self):
        self.repo = InventarisRepository(":memory:")
        self.svc = InventarisService(self.repo)
        self.kat = self.svc.tambah_kategori("Minuman").id_kategori
        self.svc.tambah_barang("B001", "Teh Botol", self.kat, 3000, 5000, 10, 3, "ADM001")
        self.svc.tambah_barang("B002", "Air Mineral", self.kat, 2000, 3500, 5, 2, "ADM001")

    def test_tambah_barang_dan_stok_awal_tercatat(self):
        self.assertEqual(self.svc.lihat_barang("B001").stok, 10)
        r = self.svc.riwayat_pergerakan("B001")
        self.assertEqual(len(r), 1); self.assertEqual(r[0].jenis, JenisPergerakan.MASUK)

    def test_id_duplikat_ditolak(self):
        with self.assertRaises(DataDuplikatError):
            self.svc.tambah_barang("B001", "X", self.kat, 1, 2)

    def test_validasi_harga_negatif(self):
        with self.assertRaises(ValidasiError):
            self.svc.ubah_harga("B001", -1, 5000)

    def test_barang_masuk_menambah_stok_dan_riwayat(self):
        self.svc.barang_masuk("B001", 5, "Supplier A", "ADM001")
        self.assertEqual(self.svc.lihat_barang("B001").stok, 15)
        self.assertEqual(self.svc.riwayat_pergerakan("B001")[-1].jumlah, 5)

    def test_barang_masuk_jumlah_nol_ditolak(self):
        with self.assertRaises(ValidasiError):
            self.svc.barang_masuk("B001", 0, "Supplier A", "ADM001")

    def test_penjualan_stok_cukup(self):
        self.svc.kurangi_stok_penjualan([("B001", 4)], "KSR001", "TRX1")
        self.assertEqual(self.svc.lihat_barang("B001").stok, 6)

    def test_penjualan_melebihi_stok_ditolak(self):
        with self.assertRaises(StokTidakCukupError):
            self.svc.kurangi_stok_penjualan([("B001", 11)], "KSR001", "TRX1")
        self.assertEqual(self.svc.lihat_barang("B001").stok, 10)

    def test_penjualan_multi_item_atomik(self):
        # item kedua gagal -> item pertama harus rollback
        with self.assertRaises(StokTidakCukupError):
            self.svc.kurangi_stok_penjualan([("B001", 2), ("B002", 99)], "KSR001", "TRX2")
        self.assertEqual(self.svc.lihat_barang("B001").stok, 10)
        self.assertEqual(len(self.svc.riwayat_pergerakan("B001", "KELUAR_JUAL")), 0)

    def test_barang_rusak_wajib_alasan(self):
        with self.assertRaises(ValidasiError):
            self.svc.barang_keluar("B001", 1, "KELUAR_RUSAK", "  ", "ADM001")
        self.svc.barang_keluar("B001", 1, "KELUAR_RUSAK", "Botol pecah", "ADM001")
        self.assertEqual(self.svc.lihat_barang("B001").stok, 9)

    def test_pembatalan_mengembalikan_stok(self):
        self.svc.kurangi_stok_penjualan([("B001", 3)], "KSR001", "TRX3")
        self.svc.kembalikan_stok_pembatalan([("B001", 3)], "ADM001", "TRX3")
        self.assertEqual(self.svc.lihat_barang("B001").stok, 10)

    def test_nonaktif_tidak_muncul_dan_tidak_bisa_dijual(self):
        self.svc.nonaktifkan_barang("B002")
        self.assertEqual([b.id_barang for b in self.svc.cari_barang()], ["B001"])
        with self.assertRaises(ValidasiError):
            self.svc.kurangi_stok_penjualan([("B002", 1)], "KSR001", "TRX4")
        self.assertFalse(self.svc.cek_stok_tersedia("B002", 1))

    def test_stok_menipis(self):
        self.svc.barang_keluar("B002", 3, "KELUAR_HILANG", "Hilang", "ADM001")
        self.assertEqual([b.id_barang for b in self.svc.daftar_stok_menipis()], ["B002"])

    def test_barang_tidak_ditemukan(self):
        with self.assertRaises(BarangTidakDitemukanError):
            self.svc.lihat_barang("ZZZ")

    def test_cari_barang(self):
        self.assertEqual(len(self.svc.cari_barang("teh")), 1)


if __name__ == "__main__":
    unittest.main()
