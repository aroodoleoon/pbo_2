import sqlite3
from contextlib import contextmanager
from .barang import Barang
from .kategori_barang import KategoriBarang
from .pergerakan_stok import PergerakanStok, JenisPergerakan
from .exceptions import DataDuplikatError, BarangTidakDitemukanError

SKEMA = """
CREATE TABLE IF NOT EXISTS kategori_barang(
    id_kategori INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL UNIQUE COLLATE NOCASE);
CREATE TABLE IF NOT EXISTS barang(
    id_barang TEXT PRIMARY KEY,
    nama TEXT NOT NULL,
    id_kategori INTEGER REFERENCES kategori_barang(id_kategori),
    harga_beli REAL NOT NULL CHECK(harga_beli >= 0),
    harga_jual REAL NOT NULL CHECK(harga_jual >= 0),
    stok INTEGER NOT NULL DEFAULT 0 CHECK(stok >= 0),
    stok_minimum INTEGER NOT NULL DEFAULT 0 CHECK(stok_minimum >= 0),
    aktif INTEGER NOT NULL DEFAULT 1);
CREATE TABLE IF NOT EXISTS pergerakan_stok(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_barang TEXT NOT NULL REFERENCES barang(id_barang),
    jenis TEXT NOT NULL,
    jumlah INTEGER NOT NULL CHECK(jumlah > 0),
    tanggal TEXT NOT NULL,
    alasan TEXT NOT NULL,
    id_karyawan TEXT NOT NULL,
    referensi TEXT);
"""


class InventarisRepository:
    """Satu-satunya kelas yang bicara langsung ke SQLite untuk modul inventaris."""

    def __init__(self, db_path=":memory:"):
        self.conn = sqlite3.connect(db_path)
        self.conn.row_factory = sqlite3.Row
        self.conn.isolation_level = None   # transaksi dikelola manual
        self.conn.execute("PRAGMA foreign_keys = ON")
        self.conn.executescript(SKEMA)
        self._depth = 0

    @contextmanager
    def transaksi(self):
        """Atomik: semua operasi di dalam blok commit bersama atau rollback bersama.
        Boleh bersarang (dipakai oleh transaksi POS milik Anggota 3/5)."""
        if self._depth == 0:
            self.conn.execute("BEGIN IMMEDIATE")
        self._depth += 1
        try:
            yield self
        except BaseException:
            self._depth -= 1
            if self._depth == 0:
                self.conn.execute("ROLLBACK")
            raise
        else:
            self._depth -= 1
            if self._depth == 0:
                self.conn.execute("COMMIT")

    def tutup(self):
        self.conn.close()

    # ---------- kategori ----------
    def simpan_kategori(self, k: KategoriBarang):
        try:
            with self.transaksi():
                cur = self.conn.execute("INSERT INTO kategori_barang(nama) VALUES(?)", (k.nama,))
            k.id_kategori = cur.lastrowid
            return k
        except sqlite3.IntegrityError:
            raise DataDuplikatError(f"Kategori '{k.nama}' sudah ada.")

    def daftar_kategori(self):
        rows = self.conn.execute("SELECT * FROM kategori_barang ORDER BY nama").fetchall()
        return [KategoriBarang(r["nama"], r["id_kategori"]) for r in rows]

    def kategori_ada(self, id_kategori):
        return self.conn.execute("SELECT 1 FROM kategori_barang WHERE id_kategori=?",
                                 (id_kategori,)).fetchone() is not None

    # ---------- barang ----------
    @staticmethod
    def _ke_barang(r):
        return Barang(r["id_barang"], r["nama"], r["id_kategori"], r["harga_beli"],
                      r["harga_jual"], r["stok"], r["stok_minimum"], bool(r["aktif"]))

    def tambah_barang(self, b: Barang):
        try:
            with self.transaksi():
                self.conn.execute(
                    "INSERT INTO barang VALUES(?,?,?,?,?,?,?,?)",
                    (b.id_barang, b.nama, b.id_kategori, b.harga_beli, b.harga_jual,
                     b.stok, b.stok_minimum, int(b.aktif)))
        except sqlite3.IntegrityError:
            raise DataDuplikatError(f"ID barang '{b.id_barang}' sudah dipakai.")

    def ambil_barang(self, id_barang):
        r = self.conn.execute("SELECT * FROM barang WHERE id_barang=?", (id_barang,)).fetchone()
        if r is None:
            raise BarangTidakDitemukanError(f"Barang '{id_barang}' tidak ditemukan.")
        return self._ke_barang(r)

    def daftar_barang(self, keyword="", id_kategori=None, hanya_aktif=True):
        sql, p = "SELECT * FROM barang WHERE 1=1", []
        if keyword:
            sql += " AND (nama LIKE ? OR id_barang LIKE ?)"
            p += [f"%{keyword}%"] * 2
        if id_kategori is not None:
            sql += " AND id_kategori=?"
            p.append(id_kategori)
        if hanya_aktif:
            sql += " AND aktif=1"
        return [self._ke_barang(r) for r in self.conn.execute(sql + " ORDER BY nama", p)]

    def simpan_perubahan(self, b: Barang):
        """Menyimpan seluruh field barang (termasuk stok & harga)."""
        with self.transaksi():
            self.conn.execute(
                "UPDATE barang SET nama=?, id_kategori=?, harga_beli=?, harga_jual=?, "
                "stok=?, stok_minimum=?, aktif=? WHERE id_barang=?",
                (b.nama, b.id_kategori, b.harga_beli, b.harga_jual, b.stok,
                 b.stok_minimum, int(b.aktif), b.id_barang))

    # ---------- pergerakan stok ----------
    def catat_pergerakan(self, p: PergerakanStok):
        with self.transaksi():
            cur = self.conn.execute(
                "INSERT INTO pergerakan_stok(id_barang,jenis,jumlah,tanggal,alasan,id_karyawan,referensi) "
                "VALUES(?,?,?,?,?,?,?)",
                (p.id_barang, p.jenis.value, p.jumlah, p.tanggal, p.alasan,
                 p.id_karyawan, p.referensi))
        p.id = cur.lastrowid
        return p

    def riwayat(self, id_barang=None, jenis=None, dari=None, sampai=None):
        sql, prm = "SELECT * FROM pergerakan_stok WHERE 1=1", []
        if id_barang:
            sql += " AND id_barang=?"; prm.append(id_barang)
        if jenis:
            sql += " AND jenis=?"; prm.append(JenisPergerakan(jenis).value)
        if dari:
            sql += " AND tanggal>=?"; prm.append(dari)
        if sampai:
            sql += " AND tanggal<=?"; prm.append(sampai + "T99")
        sql += " ORDER BY id"
        return [PergerakanStok(r["id_barang"], JenisPergerakan(r["jenis"]), r["jumlah"],
                               r["tanggal"], r["alasan"], r["id_karyawan"],
                               r["referensi"], r["id"])
                for r in self.conn.execute(sql, prm)]
