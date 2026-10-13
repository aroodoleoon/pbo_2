from dataclasses import dataclass
from typing import Optional
from .exceptions import ValidasiError


@dataclass
class KategoriBarang:
    nama: str
    id_kategori: Optional[int] = None

    def __post_init__(self):
        self.nama = (self.nama or "").strip()
        if not self.nama:
            raise ValidasiError("Nama kategori tidak boleh kosong.")
