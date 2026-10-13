from datetime import datetime
from .barang import Barang
from .kategori_barang import KategoriBarang
from .pergerakan_stok import PergerakanStok, JenisPergerakan
from .exceptions import ValidasiError, StokTidakCukupError, BarangTidakDitemukanError


class InventarisService:
    """SATU-SATUNYA sumber logika stok. Modul Transaksi (Anggota 3) dan Pembayaran/DB
    (Anggota 5) memanggil kelas ini, bukan mengubah stok sendiri.

    Setiap perubahan stok = update stok + catat pergerakan dalam satu transaksi DB."""

    def __init__(self, repo):
        self.repo = repo

    @staticmethod
    def _sekarang():
        return datetime.now().isoformat(timespec="seconds")

    # ================= KATEGORI =================
    def tambah_kategori(self, nama):
        return self.repo.simpan_kategori(KategoriBarang(nama))

    def daftar_kategori(self):
        return self.repo.daftar_kategori()

    # ================= DATA BARANG =================
    def tambah_barang(self, id_barang, nama, id_kategori, harga_beli, harga_jual,
                      stok_awal=0, stok_minimum=0, id_karyawan="SYSTEM"):
        if id_kategori is not None and not self.repo.kategori_ada(id_kategori):
            raise ValidasiError("Kategori tidak ditemukan.")
        b = Barang(id_barang, nama, id_kategori, harga_beli, harga_jual, 0, stok_minimum)
        with self.repo.transaksi():
            self.repo.tambah_barang(b)
            if stok_awal:
                self.barang_masuk(b.id_barang, stok_awal, "Stok awal", id_karyawan,
                                  alasan="Stok awal")
        return self.repo.ambil_barang(b.id_barang)

    def lihat_barang(self, id_barang):
        return self.repo.ambil_barang(id_barang)

    def cari_barang(self, keyword="", id_kategori=None, termasuk_nonaktif=False):
        return self.repo.daftar_barang(keyword, id_kategori, not termasuk_nonaktif)

    def ubah_barang(self, id_barang, nama=None, id_kategori=None, stok_minimum=None):
        b = self.repo.ambil_barang(id_barang)
        if nama is not None:
            b.nama = nama
        if id_kategori is not None:
            if not self.repo.kategori_ada(id_kategori):
                raise ValidasiError("Kategori tidak ditemukan.")
            b.id_kategori = id_kategori
        if stok_minimum is not None:
            b.stok_minimum = stok_minimum
        self.repo.simpan_perubahan(b)
        return b

    def ubah_harga(self, id_barang, harga_beli, harga_jual):
        """Pembatasan hak akses (hanya Admin) dicek di lapisan menu/Autentikasi
        milik Anggota 4. Harga lama transaksi tidak berubah karena transaksi
        menyimpan snapshot harga sendiri."""
        b = self.repo.ambil_barang(id_barang)
        b.ubah_harga(harga_beli, harga_jual)
        self.repo.simpan_perubahan(b)
        return b

    def nonaktifkan_barang(self, id_barang):
        b = self.repo.ambil_barang(id_barang)
        b.nonaktifkan()
        self.repo.simpan_perubahan(b)

    def aktifkan_barang(self, id_barang):
        b = self.repo.ambil_barang(id_barang)
        b.aktifkan()
        self.repo.simpan_perubahan(b)

    def daftar_stok_menipis(self):
        return [b for b in self.repo.daftar_barang() if b.stok_menipis()]

    # ================= KONTRAK UNTUK MODUL LAIN =================
    def cek_stok_tersedia(self, id_barang, jumlah):
        """Dipakai Transaksi POS untuk validasi sebelum menambah item."""
        return self.repo.ambil_barang(id_barang).stok_cukup(jumlah)

    def ambil_harga_jual(self, id_barang):
        """Dipakai Transaksi POS untuk snapshot hargaSaatTransaksi."""
        return self.repo.ambil_barang(id_barang).harga_jual

    def kurangi_stok_penjualan(self, items, id_karyawan, id_transaksi):
        """items: list of (id_barang, jumlah). Atomik: jika satu item gagal,
        tidak ada stok yang berubah. Dipanggil saat pembayaran berhasil."""
        with self.repo.transaksi():
            for id_barang, jumlah in items:
                b = self.repo.ambil_barang(id_barang)
                if not b.aktif:
                    raise ValidasiError(f"Barang '{b.nama}' nonaktif dan tidak bisa dijual.")
                self._ubah_stok(b, JenisPergerakan.KELUAR_JUAL, jumlah,
                                f"Penjualan {id_transaksi}", id_karyawan, id_transaksi)

    def kembalikan_stok_pembatalan(self, items, id_karyawan, id_transaksi):
        """Stok kembali saat transaksi yang sudah dibayar dibatalkan."""
        with self.repo.transaksi():
            for id_barang, jumlah in items:
                b = self.repo.ambil_barang(id_barang)
                self._ubah_stok(b, JenisPergerakan.BATAL_JUAL, jumlah,
                                f"Pembatalan {id_transaksi}", id_karyawan, id_transaksi)

    # ================= BARANG MASUK / KELUAR =================
    def barang_masuk(self, id_barang, jumlah, sumber, id_karyawan, alasan=None, referensi=None):
        sumber = (sumber or "").strip()
        if not sumber:
            raise ValidasiError("Sumber barang masuk wajib diisi.")
        with self.repo.transaksi():
            b = self.repo.ambil_barang(id_barang)
            return self._ubah_stok(b, JenisPergerakan.MASUK, jumlah,
                                   alasan or f"Penerimaan dari {sumber}", id_karyawan, referensi)

    def barang_keluar(self, id_barang, jumlah, jenis, alasan, id_karyawan, referensi=None):
        """Barang keluar non-penjualan: rusak / hilang / lain-lain. Alasan wajib."""
        jenis = JenisPergerakan(jenis)
        if jenis not in (JenisPergerakan.KELUAR_RUSAK, JenisPergerakan.KELUAR_HILANG,
                         JenisPergerakan.KELUAR_LAIN):
            raise ValidasiError("Jenis barang keluar tidak valid (gunakan RUSAK/HILANG/LAIN).")
        if not (alasan or "").strip():
            raise ValidasiError("Alasan pengurangan stok wajib diisi.")
        with self.repo.transaksi():
            b = self.repo.ambil_barang(id_barang)
            return self._ubah_stok(b, jenis, jumlah, alasan.strip(), id_karyawan, referensi)

    # ================= RIWAYAT =================
    def riwayat_pergerakan(self, id_barang=None, jenis=None, dari=None, sampai=None):
        return self.repo.riwayat(id_barang, jenis, dari, sampai)

    # ================= INTERNAL =================
    def _ubah_stok(self, b: Barang, jenis, jumlah, alasan, id_karyawan, referensi):
        if not (id_karyawan or "").strip():
            raise ValidasiError("Petugas (id_karyawan) wajib dicatat.")
        if jenis.menambah:
            b.tambah_stok(jumlah)
        else:
            b.kurangi_stok(jumlah)      # melempar StokTidakCukupError bila kurang
        self.repo.simpan_perubahan(b)
        return self.repo.catat_pergerakan(PergerakanStok(
            b.id_barang, jenis, jumlah, self._sekarang(), alasan, id_karyawan, referensi))
