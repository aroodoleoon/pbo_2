# Class Diagram — rancangan awal

```mermaid
classDiagram
    class Karyawan {
      +UUID id
      +String nama
      +String username
      +String passwordHash
      +Role role
      +Boolean aktif
    }
    class Admin
    class Kasir
    Karyawan <|-- Admin
    Karyawan <|-- Kasir

    class Barang {
      +UUID id
      +String kode
      +String nama
      +UUID kategoriId
      +Long hargaBeli
      +Long hargaJual
      +Int stok
      +Int stokMinimum
      +Boolean aktif
      +tambahStok(jumlah)
      +kurangiStok(jumlah)
    }
    class KategoriBarang {
      +UUID id
      +String nama
    }
    KategoriBarang "1" --> "0..*" Barang

    class Pelanggan {
      +UUID id
      +String nama
      +String kontak
    }
    class Member {
      +UUID id
      +String nomorMember
      +Int poin
    }
    Pelanggan <|-- Member

    class Transaksi {
      +UUID id
      +String nomorTransaksi
      +Instant dibuatPada
      +UUID kasirId
      +UUID pelangganId
      +Long subtotal
      +Long diskon
      +Long pajak
      +Long total
      +String keteranganDiskon
      +TransactionStatus status
      +hitungTotal(aturanDiskon)
    }
    class AturanDiskon {
      <<interface>>
      +memenuhiSyarat(transaksi, pelanggan) Boolean
      +hitungDiskon(subtotal) Long
      +keterangan() String
    }
    class DetailTransaksi {
      +UUID id
      +UUID barangId
      +Int jumlah
      +Long hargaSaatTransaksi
      +Long subtotal
    }
    class MetodePembayaran {
      <<enumeration>>
      CASH
      QRIS
      TRANSFER
      DEBIT
    }
    class Pembayaran {
      +UUID id
      +UUID transaksiId
      +MetodePembayaran metode
      +Long nominalBayar
      +Long kembalian
      +PaymentStatus status
    }
    class PergerakanStok {
      +UUID id
      +UUID barangId
      +UUID karyawanId
      +String jenis
      +Int jumlah
      +String alasan
      +Instant dibuatPada
    }
    Transaksi "1" *-- "1..*" DetailTransaksi
    Transaksi ..> AturanDiskon : menggunakan
    DetailTransaksi "*" --> "1" Barang
    Transaksi "*" --> "1" Kasir
    Transaksi "0..*" --> "0..1" Pelanggan
    Transaksi "1" --> "0..1" Pembayaran
    Pembayaran --> MetodePembayaran
    PergerakanStok "*" --> "1" Barang
    PergerakanStok "*" --> "1" Karyawan
```

## Catatan desain
- Diagram ini adalah rancangan konseptual untuk disepakati, bukan implementasi final.
- Role Admin/Kasir dapat berupa enum alih-alih subclass jika perilakunya tidak berbeda. Tim harus memilih satu pola dan konsisten.
- Gunakan tipe uang integer dalam satuan rupiah untuk menghindari error floating point.
- UUID adalah rekomendasi untuk ID internal.
- Hindari inheritance yang tidak dibutuhkan hanya demi menunjukkan konsep OOP.
