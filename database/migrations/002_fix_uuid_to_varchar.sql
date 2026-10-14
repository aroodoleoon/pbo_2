-- Migrasi 002: Mengubah kolom UUID menjadi VARCHAR(80) agar kompatibel dengan seluruh format ID
ALTER TABLE barang DROP CONSTRAINT IF EXISTS barang_kategori_id_fkey;
ALTER TABLE member DROP CONSTRAINT IF EXISTS member_pelanggan_id_fkey;
ALTER TABLE transaksi DROP CONSTRAINT IF EXISTS transaksi_kasir_id_fkey;
ALTER TABLE transaksi DROP CONSTRAINT IF EXISTS transaksi_pelanggan_id_fkey;
ALTER TABLE detail_transaksi DROP CONSTRAINT IF EXISTS detail_transaksi_transaksi_id_fkey;
ALTER TABLE detail_transaksi DROP CONSTRAINT IF EXISTS detail_transaksi_barang_id_fkey;
ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_transaksi_id_fkey;
ALTER TABLE pergerakan_stok DROP CONSTRAINT IF EXISTS pergerakan_stok_barang_id_fkey;
ALTER TABLE pergerakan_stok DROP CONSTRAINT IF EXISTS pergerakan_stok_karyawan_id_fkey;
ALTER TABLE audit_logs DROP CONSTRAINT IF EXISTS audit_logs_actor_id_fkey;

ALTER TABLE karyawan ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE kategori_barang ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE barang ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE barang ALTER COLUMN kategori_id TYPE VARCHAR(80);
ALTER TABLE pelanggan ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE member ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE member ALTER COLUMN pelanggan_id TYPE VARCHAR(80);
ALTER TABLE transaksi ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE transaksi ALTER COLUMN kasir_id TYPE VARCHAR(80);
ALTER TABLE transaksi ALTER COLUMN pelanggan_id TYPE VARCHAR(80);
ALTER TABLE detail_transaksi ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE detail_transaksi ALTER COLUMN transaksi_id TYPE VARCHAR(80);
ALTER TABLE detail_transaksi ALTER COLUMN barang_id TYPE VARCHAR(80);
ALTER TABLE pembayaran ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE pembayaran ALTER COLUMN transaksi_id TYPE VARCHAR(80);
ALTER TABLE pergerakan_stok ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE pergerakan_stok ALTER COLUMN barang_id TYPE VARCHAR(80);
ALTER TABLE pergerakan_stok ALTER COLUMN karyawan_id TYPE VARCHAR(80);
ALTER TABLE audit_logs ALTER COLUMN id TYPE VARCHAR(80);
ALTER TABLE audit_logs ALTER COLUMN actor_id TYPE VARCHAR(80);

ALTER TABLE barang ADD CONSTRAINT barang_kategori_id_fkey FOREIGN KEY (kategori_id) REFERENCES kategori_barang(id);
ALTER TABLE member ADD CONSTRAINT member_pelanggan_id_fkey FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id);
ALTER TABLE transaksi ADD CONSTRAINT transaksi_kasir_id_fkey FOREIGN KEY (kasir_id) REFERENCES karyawan(id);
ALTER TABLE transaksi ADD CONSTRAINT transaksi_pelanggan_id_fkey FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id);
ALTER TABLE detail_transaksi ADD CONSTRAINT detail_transaksi_transaksi_id_fkey FOREIGN KEY (transaksi_id) REFERENCES transaksi(id);
ALTER TABLE detail_transaksi ADD CONSTRAINT detail_transaksi_barang_id_fkey FOREIGN KEY (barang_id) REFERENCES barang(id);
ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_transaksi_id_fkey FOREIGN KEY (transaksi_id) REFERENCES transaksi(id);
ALTER TABLE pergerakan_stok ADD CONSTRAINT pergerakan_stok_barang_id_fkey FOREIGN KEY (barang_id) REFERENCES barang(id);
ALTER TABLE pergerakan_stok ADD CONSTRAINT pergerakan_stok_karyawan_id_fkey FOREIGN KEY (karyawan_id) REFERENCES karyawan(id);
ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_actor_id_fkey FOREIGN KEY (actor_id) REFERENCES karyawan(id);

