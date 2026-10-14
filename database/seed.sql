-- Data Dummy Seed Lengkap untuk Pengujian & Demo Sistem POS
-- Password admin: admin123 (hash SHA-256: 240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9)
-- Password kasir: kasir123 (hash SHA-256: f02b7c1e519e4fa436147f7e1399974f9510aa9c8e0cb8be29151eb540f9d214)

-- 1. Karyawan (Admin, Kasir 1, Kasir 2)
INSERT INTO karyawan (id, nama, username, password_hash, role, aktif) VALUES
('11111111-1111-1111-1111-111111111111', 'Wildan Administrator', 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'ADMIN', true),
('22222222-2222-2222-2222-222222222222', 'Dapin Kasir Pagi', 'kasir1', 'f02b7c1e519e4fa436147f7e1399974f9510aa9c8e0cb8be29151eb540f9d214', 'CASHIER', true),
('33333333-2222-2222-2222-222222222222', 'Dhia Kasir Sore', 'kasir2', 'f02b7c1e519e4fa436147f7e1399974f9510aa9c8e0cb8be29151eb540f9d214', 'CASHIER', true)
ON CONFLICT (username) DO NOTHING;

-- 2. Kategori Barang
INSERT INTO kategori_barang (id, nama) VALUES
('kat-001', 'Makanan Ringan'),
('kat-002', 'Minuman'),
('kat-003', 'Kebutuhan Pokok'),
('kat-004', 'Bumbu & Dapur'),
('kat-005', 'Perawatan Diri')
ON CONFLICT (nama) DO NOTHING;

-- 3. Katalog Barang & Stok Awal
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
('brg-010', '89999991010', 'Gula Pasir Gulaku 1kg', 'kat-003', 14500, 17500, 30, 8, true)
ON CONFLICT (kode) DO NOTHING;

-- 4. Pelanggan & Member
INSERT INTO pelanggan (id, nama, kontak) VALUES
('plg-001', 'Andi Santoso', '081234567890'),
('plg-002', 'Budi Pratama (Tamu)', '081987654321'),
('plg-003', 'Siti Nurhaliza', '081377889900'),
('plg-004', 'Hendra Wijaya', '081566778899')
ON CONFLICT DO NOTHING;

INSERT INTO member (id, pelanggan_id, nomor_member, poin) VALUES
('mbr-001', 'plg-001', 'MBR-001', 150),
('mbr-003', 'plg-003', 'MBR-002', 350),
('mbr-004', 'plg-004', 'MBR-003', 800)
ON CONFLICT DO NOTHING;
