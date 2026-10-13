-- Migrasi modul pembayaran. Sepakati nama kolom bersama Anggota 2/3/4 sebelum digunakan.
-- Tabel barang, karyawan, pelanggan, dan pergerakan_stok tetap dimiliki modul lain.

CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi VARCHAR(80) PRIMARY KEY,
    tanggal_transaksi TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_kasir VARCHAR(80) NOT NULL,
    id_pelanggan VARCHAR(80) NULL,
    subtotal BIGINT NOT NULL CHECK (subtotal >= 0),
    diskon BIGINT NOT NULL DEFAULT 0 CHECK (diskon >= 0),
    total BIGINT NOT NULL CHECK (total >= 0),
    status VARCHAR(20) NOT NULL CHECK (status IN ('DRAFT', 'LUNAS', 'DIBATALKAN')),
    CHECK (diskon <= subtotal),
    CHECK (total = subtotal - diskon)
);

CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail VARCHAR(100) PRIMARY KEY,
    id_transaksi VARCHAR(80) NOT NULL REFERENCES transaksi(id_transaksi),
    id_barang VARCHAR(80) NOT NULL,
    nama_barang_snapshot VARCHAR(200) NOT NULL,
    jumlah INTEGER NOT NULL CHECK (jumlah > 0),
    harga_saat_transaksi BIGINT NOT NULL CHECK (harga_saat_transaksi >= 0),
    subtotal BIGINT NOT NULL CHECK (subtotal = jumlah * harga_saat_transaksi)
);

CREATE INDEX IF NOT EXISTS idx_transaksi_tanggal ON transaksi(tanggal_transaksi DESC);
CREATE INDEX IF NOT EXISTS idx_detail_transaksi_transaksi ON detail_transaksi(id_transaksi);

CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran VARCHAR(100) PRIMARY KEY,
    id_transaksi VARCHAR(80) NOT NULL UNIQUE REFERENCES transaksi(id_transaksi),
    metode VARCHAR(20) NOT NULL CHECK (metode IN ('TUNAI')),
    nominal_bayar BIGINT NOT NULL CHECK (nominal_bayar >= 0),
    kembalian BIGINT NOT NULL CHECK (kembalian >= 0),
    status VARCHAR(20) NOT NULL CHECK (status IN ('BERHASIL', 'GAGAL', 'DIBATALKAN')),
    tanggal_pembayaran TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_pembayaran_status ON pembayaran(status);
