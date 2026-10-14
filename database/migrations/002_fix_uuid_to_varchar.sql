-- Migrasi penyesuaian tipe UUID ke VARCHAR(80) untuk database PostgreSQL yang sudah berjalan
ALTER TABLE IF EXISTS detail_transaksi ALTER COLUMN barang_id TYPE VARCHAR(80);
ALTER TABLE IF EXISTS pergerakan_stok ALTER COLUMN barang_id TYPE VARCHAR(80);
ALTER TABLE IF EXISTS barang ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE IF EXISTS barang ALTER COLUMN kategori_id TYPE VARCHAR(80);
ALTER TABLE IF EXISTS kategori_barang ALTER COLUMN id TYPE VARCHAR(80);
