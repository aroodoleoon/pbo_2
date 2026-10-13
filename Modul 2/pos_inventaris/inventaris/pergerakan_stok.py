from dataclasses import dataclass
from enum import Enum
from typing import Optional


class JenisPergerakan(str, Enum):
    MASUK = "MASUK"                    # barang masuk / penerimaan / stok awal
    KELUAR_JUAL = "KELUAR_JUAL"        # keluar karena penjualan
    KELUAR_RUSAK = "KELUAR_RUSAK"      # barang rusak
    KELUAR_HILANG = "KELUAR_HILANG"    # barang hilang
    KELUAR_LAIN = "KELUAR_LAIN"        # alasan lain (wajib keterangan)
    BATAL_JUAL = "BATAL_JUAL"          # stok kembali karena transaksi dibatalkan

    @property
    def menambah(self):
        return self in (JenisPergerakan.MASUK, JenisPergerakan.BATAL_JUAL)


@dataclass
class PergerakanStok:
    id_barang: str
    jenis: JenisPergerakan
    jumlah: int
    tanggal: str
    alasan: str
    id_karyawan: str
    referensi: Optional[str] = None   # mis. id_transaksi / no. surat jalan
    id: Optional[int] = None
