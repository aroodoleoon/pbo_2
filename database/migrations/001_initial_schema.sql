-- Skema Database POS Academic (PostgreSQL 16/17 & H2 Compatible)
CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE IF NOT EXISTS karyawan (
    id VARCHAR(80) PRIMARY KEY,
    nama TEXT NOT NULL,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('ADMIN', 'CASHIER')),
    aktif BOOLEAN NOT NULL DEFAULT TRUE,
    dibuat_pada TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS kategori_barang (
    id VARCHAR(80) PRIMARY KEY,
    nama TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS barang (
    id VARCHAR(80) PRIMARY KEY,
    kode TEXT NOT NULL UNIQUE,
    nama TEXT NOT NULL,
    kategori_id VARCHAR(80) REFERENCES kategori_barang(id),
    harga_beli BIGINT NOT NULL CHECK (harga_beli >= 0),
    harga_jual BIGINT NOT NULL CHECK (harga_jual >= 0),
    stok INTEGER NOT NULL DEFAULT 0 CHECK (stok >= 0),
    stok_minimum INTEGER NOT NULL DEFAULT 0 CHECK (stok_minimum >= 0),
    aktif BOOLEAN NOT NULL DEFAULT TRUE,
    dibuat_pada TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS pelanggan (
    id VARCHAR(80) PRIMARY KEY,
    nama TEXT NOT NULL,
    kontak TEXT,
    dibuat_pada TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS member (
    id VARCHAR(80) PRIMARY KEY,
    pelanggan_id VARCHAR(80) NOT NULL UNIQUE REFERENCES pelanggan(id),
    nomor_member TEXT NOT NULL UNIQUE,
    poin INTEGER NOT NULL DEFAULT 0 CHECK (poin >= 0)
);

CREATE TABLE IF NOT EXISTS transaksi (
    id VARCHAR(80) PRIMARY KEY,
    nomor_transaksi TEXT NOT NULL UNIQUE,
    kasir_id VARCHAR(80) NOT NULL REFERENCES karyawan(id),
    pelanggan_id VARCHAR(80) REFERENCES pelanggan(id),
    subtotal BIGINT NOT NULL DEFAULT 0 CHECK (subtotal >= 0),
    diskon BIGINT NOT NULL DEFAULT 0 CHECK (diskon >= 0),
    pajak BIGINT NOT NULL DEFAULT 0 CHECK (pajak >= 0),
    total BIGINT NOT NULL DEFAULT 0 CHECK (total >= 0),
    keterangan_syarat_diskon TEXT,
    status TEXT NOT NULL CHECK (status IN ('PENDING', 'COMPLETED', 'CANCELLED')),
    dibuat_pada TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS detail_transaksi (
    id VARCHAR(80) PRIMARY KEY,
    transaksi_id VARCHAR(80) NOT NULL REFERENCES transaksi(id),
    barang_id VARCHAR(80) NOT NULL REFERENCES barang(id),
    jumlah INTEGER NOT NULL CHECK (jumlah > 0),
    harga_saat_transaksi BIGINT NOT NULL CHECK (harga_saat_transaksi >= 0),
    subtotal BIGINT NOT NULL CHECK (subtotal >= 0)
);

CREATE TABLE IF NOT EXISTS pembayaran (
    id VARCHAR(80) PRIMARY KEY,
    transaksi_id VARCHAR(80) NOT NULL UNIQUE REFERENCES transaksi(id),
    metode TEXT NOT NULL CHECK (metode IN ('CASH', 'QRIS', 'TRANSFER', 'DEBIT')),
    nominal_bayar BIGINT NOT NULL CHECK (nominal_bayar >= 0),
    kembalian BIGINT NOT NULL CHECK (kembalian >= 0),
    status TEXT NOT NULL CHECK (status IN ('PENDING', 'SUCCESS', 'FAILED')),
    dibuat_pada TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS pergerakan_stok (
    id VARCHAR(80) PRIMARY KEY,
    barang_id VARCHAR(80) NOT NULL REFERENCES barang(id),
    karyawan_id VARCHAR(80) NOT NULL REFERENCES karyawan(id),
    jenis TEXT NOT NULL CHECK (jenis IN ('IN', 'OUT', 'ADJUSTMENT', 'OUT_SALE', 'OUT_DAMAGED', 'OUT_LOST', 'OUT_OTHER', 'CANCEL_SALE')),
    jumlah INTEGER NOT NULL CHECK (jumlah > 0),
    alasan TEXT NOT NULL,
    dibuat_pada TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id VARCHAR(80) PRIMARY KEY,
    timestamp TIMESTAMPTZ NOT NULL DEFAULT now(),
    actor_id VARCHAR(80) REFERENCES karyawan(id),
    action TEXT NOT NULL,
    entity_type TEXT NOT NULL,
    entity_id TEXT,
    result TEXT NOT NULL,
    metadata JSONB,
    request_id TEXT
);

CREATE INDEX IF NOT EXISTS idx_transaksi_dibuat_pada ON transaksi(dibuat_pada);
CREATE INDEX IF NOT EXISTS idx_detail_transaksi_transaksi ON detail_transaksi(transaksi_id);
CREATE INDEX IF NOT EXISTS idx_pergerakan_stok_barang_waktu ON pergerakan_stok(barang_id, dibuat_pada);
CREATE INDEX IF NOT EXISTS idx_audit_logs_timestamp ON audit_logs(timestamp);
