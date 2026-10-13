# ERD PostgreSQL — rancangan awal

```mermaid
erDiagram
    KATEGORI_BARANG ||--o{ BARANG : mengelompokkan
    KARYAWAN ||--o{ TRANSAKSI : melayani
    PELANGGAN o|--o{ TRANSAKSI : melakukan
    TRANSAKSI ||--|{ DETAIL_TRANSAKSI : memiliki
    BARANG ||--o{ DETAIL_TRANSAKSI : dijual
    TRANSAKSI ||--o| PEMBAYARAN : dibayar
    BARANG ||--o{ PERGERAKAN_STOK : memiliki
    KARYAWAN ||--o{ PERGERAKAN_STOK : mencatat
    PELANGGAN ||--o| MEMBER : menjadi

    KARYAWAN {
      uuid id PK
      text nama
      text username UK
      text password_hash
      text role
      boolean aktif
    }
    KATEGORI_BARANG {
      uuid id PK
      text nama
    }
    BARANG {
      uuid id PK
      text kode UK
      text nama
      uuid kategori_id FK
      bigint harga_beli
      bigint harga_jual
      integer stok
      integer stok_minimum
      boolean aktif
    }
    PELANGGAN {
      uuid id PK
      text nama
      text kontak
    }
    MEMBER {
      uuid id PK
      uuid pelanggan_id FK
      text nomor_member UK
      integer poin
    }
    TRANSAKSI {
      uuid id PK
      text nomor_transaksi UK
      uuid kasir_id FK
      uuid pelanggan_id FK
      bigint subtotal
      bigint diskon
      bigint pajak
      bigint total
      text keterangan_syarat_diskon
      text status
      timestamptz dibuat_pada
    }
    DETAIL_TRANSAKSI {
      uuid id PK
      uuid transaksi_id FK
      uuid barang_id FK
      integer jumlah
      bigint harga_saat_transaksi
      bigint subtotal
    }
    PEMBAYARAN {
      uuid id PK
      uuid transaksi_id FK
      text metode
      bigint nominal_bayar
      bigint kembalian
      text status
    }
    PERGERAKAN_STOK {
      uuid id PK
      uuid barang_id FK
      uuid karyawan_id FK
      text jenis
      integer jumlah
      text alasan
      timestamptz dibuat_pada
    }
    AUDIT_LOG {
      uuid id PK
      timestamptz timestamp
      uuid actor_id FK
      text action
      text entity_type
      text entity_id
      text result
      jsonb metadata
      text request_id
    }
    KARYAWAN ||--o{ AUDIT_LOG : menghasilkan
```

## Audit Log (PostgreSQL `JSONB`)
Tabel `audit_logs` disimpan langsung di PostgreSQL menggunakan kolom `metadata` bertipe `JSONB` agar ringan (tanpa perlu container MongoDB terpisah) namun tetap fleksibel menyimpan struktur objek event:
- `id` (`UUID`)
- `timestamp` (`TIMESTAMPTZ`)
- `actor_id` (`UUID`, nullable)
- `action` (`TEXT`)
- `entity_type` (`TEXT`)
- `entity_id` (`TEXT`)
- `result` (`TEXT`)
- `metadata` (`JSONB`)
- `request_id` (`TEXT`)

Jangan menyimpan password, token, atau informasi rahasia di audit log.
