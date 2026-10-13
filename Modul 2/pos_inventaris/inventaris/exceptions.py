class InventarisError(Exception):
    """Basis semua error modul inventaris."""

class ValidasiError(InventarisError):
    """Data input tidak valid."""

class StokTidakCukupError(InventarisError):
    """Stok tidak mencukupi untuk operasi yang diminta."""

class BarangTidakDitemukanError(InventarisError):
    """Barang dengan ID tersebut tidak ada."""

class DataDuplikatError(InventarisError):
    """ID/nama unik sudah dipakai."""
