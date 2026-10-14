package com.pos.server.config

import org.h2.jdbcx.JdbcDataSource
import org.postgresql.ds.PGSimpleDataSource
import java.io.File
import java.sql.Connection
import javax.sql.DataSource

object DatabaseFactory {
    fun createDataSource(config: DatabaseConfig): DataSource {
        return try {
            val ds = PGSimpleDataSource()
            ds.setURL(config.url)
            ds.user = config.user
            ds.password = config.pass
            ds.setProperty("stringtype", "unspecified")

            // Test connection & migrate if necessary
            ds.connection.use { c ->
                println("[DATABASE] Berhasil terhubung ke PostgreSQL: ${config.url}")
                migratePostgresIfNeeded(c)
            }
            ds
        } catch (e: Exception) {
            println("[DATABASE WARNING] PostgreSQL tidak dapat dihubungi (${e.message}).")
            println("[DATABASE FALLBACK] Mengaktifkan In-Memory PostgreSQL Mode (H2) untuk testing instan...")
            createInMemoryDataSource()
        }
    }

    private fun createInMemoryDataSource(): DataSource {
        val h2Ds = JdbcDataSource()
        h2Ds.setURL("jdbc:h2:mem:pos_db;MODE=PostgreSQL;DATABASE_TO_LOWER=TRUE;DEFAULT_NULL_ORDERING=HIGH;DB_CLOSE_DELAY=-1")
        h2Ds.user = "sa"
        h2Ds.password = ""

        h2Ds.connection.use { c ->
            initSchemaAndSeed(c)
        }
        println("[DATABASE READY] In-memory database siap dengan skema dan seed data!")
        return h2Ds
    }

    private fun initSchemaAndSeed(connection: Connection) {
        val schemaSql = """
            CREATE TABLE IF NOT EXISTS karyawan (
                id VARCHAR(80) PRIMARY KEY,
                nama VARCHAR(100) NOT NULL,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(200) NOT NULL,
                role VARCHAR(20) NOT NULL,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS kategori_barang (
                id VARCHAR(80) PRIMARY KEY,
                nama VARCHAR(100) NOT NULL UNIQUE
            );

            CREATE TABLE IF NOT EXISTS barang (
                id VARCHAR(80) PRIMARY KEY,
                kode VARCHAR(50) NOT NULL UNIQUE,
                nama VARCHAR(200) NOT NULL,
                kategori_id VARCHAR(80) REFERENCES kategori_barang(id),
                harga_beli BIGINT NOT NULL DEFAULT 0,
                harga_jual BIGINT NOT NULL DEFAULT 0,
                stok INT NOT NULL DEFAULT 0,
                stok_minimum INT NOT NULL DEFAULT 0,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS pelanggan (
                id VARCHAR(80) PRIMARY KEY,
                nama VARCHAR(100) NOT NULL,
                kontak VARCHAR(50),
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS member (
                id VARCHAR(80) PRIMARY KEY,
                pelanggan_id VARCHAR(80) NOT NULL UNIQUE REFERENCES pelanggan(id),
                nomor_member VARCHAR(50) NOT NULL UNIQUE,
                poin INT NOT NULL DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS transaksi (
                id VARCHAR(80) PRIMARY KEY,
                nomor_transaksi VARCHAR(80) NOT NULL UNIQUE,
                kasir_id VARCHAR(80) NOT NULL,
                pelanggan_id VARCHAR(80),
                subtotal BIGINT NOT NULL DEFAULT 0,
                diskon BIGINT NOT NULL DEFAULT 0,
                pajak BIGINT NOT NULL DEFAULT 0,
                total BIGINT NOT NULL DEFAULT 0,
                keterangan_syarat_diskon VARCHAR(255),
                status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS detail_transaksi (
                id VARCHAR(80) PRIMARY KEY,
                transaksi_id VARCHAR(80) NOT NULL REFERENCES transaksi(id),
                barang_id VARCHAR(80) NOT NULL REFERENCES barang(id),
                jumlah INT NOT NULL,
                harga_saat_transaksi BIGINT NOT NULL,
                subtotal BIGINT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS pembayaran (
                id VARCHAR(80) PRIMARY KEY,
                transaksi_id VARCHAR(80) NOT NULL UNIQUE REFERENCES transaksi(id),
                metode VARCHAR(20) NOT NULL,
                nominal_bayar BIGINT NOT NULL,
                kembalian BIGINT NOT NULL,
                status VARCHAR(20) NOT NULL,
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS pergerakan_stok (
                id VARCHAR(80) PRIMARY KEY,
                barang_id VARCHAR(80) NOT NULL REFERENCES barang(id),
                karyawan_id VARCHAR(80) NOT NULL,
                jenis VARCHAR(20) NOT NULL,
                jumlah INT NOT NULL,
                alasan VARCHAR(255) NOT NULL,
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS audit_logs (
                id VARCHAR(80) PRIMARY KEY,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                actor_id VARCHAR(80),
                action VARCHAR(100) NOT NULL,
                entity_type VARCHAR(100) NOT NULL,
                entity_id VARCHAR(80),
                result VARCHAR(50) NOT NULL,
                metadata VARCHAR(4000),
                request_id VARCHAR(80)
            );

            -- Data Dummy Seed Lengkap
            INSERT INTO karyawan (id, nama, username, password_hash, role, aktif) VALUES
            ('11111111-1111-1111-1111-111111111111', 'Wildan Administrator', 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'ADMIN', true),
            ('22222222-2222-2222-2222-222222222222', 'Dapin Kasir Pagi', 'kasir1', 'f02b7c1e519e4fa436147f7e1399974f9510aa9c8e0cb8be29151eb540f9d214', 'CASHIER', true),
            ('33333333-2222-2222-2222-222222222222', 'Dhia Kasir Sore', 'kasir2', 'f02b7c1e519e4fa436147f7e1399974f9510aa9c8e0cb8be29151eb540f9d214', 'CASHIER', true);

            INSERT INTO kategori_barang (id, nama) VALUES
            ('kat-001', 'Makanan Ringan'),
            ('kat-002', 'Minuman'),
            ('kat-003', 'Kebutuhan Pokok'),
            ('kat-004', 'Bumbu & Dapur'),
            ('kat-005', 'Perawatan Diri');

            INSERT INTO barang (id, kode, nama, kategori_id, harga_beli, harga_jual, stok, stok_minimum, aktif) VALUES
            ('brg-001', '89999991001', 'Chitato Sapi Panggang 68g', 'kat-001', 8000, 11500, 50, 10, true),
            ('brg-002', '89999991002', 'Ultra Milk Cokelat 250ml', 'kat-002', 5000, 7000, 40, 5, true),
            ('brg-003', '89999991003', 'Aqua Botol 600ml', 'kat-002', 2500, 4000, 100, 20, true),
            ('brg-004', '89999991004', 'Minyak Goreng Sania 2L', 'kat-003', 28000, 34000, 25, 5, true),
            ('brg-005', '89999991005', 'Indomie Goreng Spesial 85g', 'kat-001', 2600, 3500, 150, 24, true),
            ('brg-006', '89999991006', 'Teh Botol Sosro Kotak 250ml', 'kat-002', 3000, 4500, 60, 12, true),
            ('brg-007', '89999991007', 'Sabun Mandi Lifebuoy Total 10', 'kat-005', 4000, 5500, 35, 10, true),
            ('brg-008', '89999991008', 'Kopi Kapal Api Special Mix 24g', 'kat-002', 1500, 2500, 80, 15, true),
            ('brg-009', '89999991009', 'Roti Tawar Sari Roti 200g', 'kat-001', 12000, 15000, 20, 5, true),
            ('brg-010', '89999991010', 'Gula Pasir Gulaku 1kg', 'kat-003', 14500, 17500, 30, 8, true);

            INSERT INTO pelanggan (id, nama, kontak) VALUES
            ('plg-001', 'Andi Santoso', '081234567890'),
            ('plg-002', 'Budi Pratama (Tamu)', '081987654321'),
            ('plg-003', 'Siti Nurhaliza', '081377889900'),
            ('plg-004', 'Hendra Wijaya', '081566778899');

            INSERT INTO member (id, pelanggan_id, nomor_member, poin) VALUES
            ('mbr-001', 'plg-001', 'MBR-001', 150),
            ('mbr-003', 'plg-003', 'MBR-002', 350),
            ('mbr-004', 'plg-004', 'MBR-003', 800);
        """.trimIndent()

        connection.createStatement().use { stmt ->
            for (statement in schemaSql.split(";")) {
                val clean = statement.trim()
                if (clean.isNotEmpty()) {
                    stmt.execute(clean)
                }
            }
        }
    }

    private fun migratePostgresIfNeeded(connection: Connection) {
        try {
            connection.createStatement().use { s ->
                s.execute("""
                    DO $$ 
                    BEGIN
                        IF EXISTS (
                            SELECT 1 FROM information_schema.columns 
                            WHERE table_name = 'barang' AND column_name = 'id' AND data_type = 'uuid'
                        ) THEN
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
                        END IF;
                    END $$;
                """)
            }
        } catch (e: Exception) {
            println("[DATABASE MIGRATION NOTICE] ${e.message}")
        }
    }
}
