from .exceptions import ValidasiError, StokTidakCukupError


class Barang:
    """Entitas barang. Harga & stok di-encapsulate: hanya bisa diubah lewat metode
    yang melakukan validasi (tambah_stok, kurangi_stok, ubah_harga)."""

    def __init__(self, id_barang, nama, id_kategori, harga_beli, harga_jual,
                 stok=0, stok_minimum=0, aktif=True):
        self.id_barang = id_barang
        self.nama = nama
        self.id_kategori = id_kategori
        self._harga_beli = 0
        self._harga_jual = 0
        self._stok = 0
        self._stok_minimum = 0
        self.ubah_harga(harga_beli, harga_jual)
        self._set_stok_awal(stok)
        self.stok_minimum = stok_minimum
        self.aktif = bool(aktif)

    # ---------- properti dengan validasi ----------
    @property
    def id_barang(self):
        return self._id_barang

    @id_barang.setter
    def id_barang(self, v):
        v = (v or "").strip()
        if not v:
            raise ValidasiError("ID/kode barang tidak boleh kosong.")
        self._id_barang = v

    @property
    def nama(self):
        return self._nama

    @nama.setter
    def nama(self, v):
        v = (v or "").strip()
        if not v:
            raise ValidasiError("Nama barang tidak boleh kosong.")
        self._nama = v

    @property
    def harga_beli(self):
        return self._harga_beli

    @property
    def harga_jual(self):
        return self._harga_jual

    @property
    def stok(self):
        return self._stok

    @property
    def stok_minimum(self):
        return self._stok_minimum

    @stok_minimum.setter
    def stok_minimum(self, v):
        if not isinstance(v, int) or v < 0:
            raise ValidasiError("Stok minimum harus bilangan bulat >= 0.")
        self._stok_minimum = v

    # ---------- perilaku ----------
    def _set_stok_awal(self, v):
        if not isinstance(v, int) or v < 0:
            raise ValidasiError("Stok harus bilangan bulat >= 0.")
        self._stok = v

    @staticmethod
    def _cek_jumlah(jumlah):
        if not isinstance(jumlah, int) or isinstance(jumlah, bool) or jumlah <= 0:
            raise ValidasiError("Jumlah harus bilangan bulat > 0.")

    def tambah_stok(self, jumlah):
        self._cek_jumlah(jumlah)
        self._stok += jumlah

    def kurangi_stok(self, jumlah):
        self._cek_jumlah(jumlah)
        if jumlah > self._stok:
            raise StokTidakCukupError(
                f"Stok '{self.nama}' tidak cukup (tersedia {self._stok}, diminta {jumlah}).")
        self._stok -= jumlah

    def ubah_harga(self, harga_beli, harga_jual):
        for h in (harga_beli, harga_jual):
            if not isinstance(h, (int, float)) or isinstance(h, bool) or h < 0:
                raise ValidasiError("Harga harus angka >= 0.")
        self._harga_beli = harga_beli
        self._harga_jual = harga_jual

    def stok_cukup(self, jumlah):
        return self.aktif and 0 < jumlah <= self._stok

    def stok_menipis(self):
        return self._stok <= self._stok_minimum

    def nonaktifkan(self):
        self.aktif = False

    def aktifkan(self):
        self.aktif = True

    def __repr__(self):
        return (f"Barang({self.id_barang!r}, {self.nama!r}, stok={self.stok}, "
                f"jual={self.harga_jual}, aktif={self.aktif})")
