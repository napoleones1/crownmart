-- ============================================================
-- CrownMart v2 Migration - Tambah fitur yang kurang
-- Jalankan di phpMyAdmin setelah migrate_auth.sql
-- ============================================================
USE crownmart;

-- 1. Tambah kolom payment_method & shipping_method ke orders
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS payment_method ENUM('wallet','transfer','cod') NOT NULL DEFAULT 'wallet' AFTER address,
  ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'paid' AFTER payment_method,
  ADD COLUMN IF NOT EXISTS shipping_method ENUM('regular','express','same_day') NOT NULL DEFAULT 'regular' AFTER payment_status,
  ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER shipping_method;

-- 2. Perbaiki ENUM status order agar ada Pending
ALTER TABLE orders MODIFY status ENUM('Pending','Processing','Shipped','Out for Delivery','Delivered') NOT NULL DEFAULT 'Pending';

-- 3. Tabel payments (riwayat transaksi keuangan)
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(30) NOT NULL,
  user_id INT NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  method ENUM('wallet','transfer','cod') NOT NULL,
  status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
  proof_image VARCHAR(500) NULL COMMENT 'URL bukti transfer',
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- 4. Tabel categories (standar e-commerce)
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE,
  icon VARCHAR(20) DEFAULT '📦',
  description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO categories (name, slug, icon) VALUES
('Electronics',     'electronics',     '💻'),
('Fashion',         'fashion',         '👗'),
('Home & Kitchen',  'home-kitchen',    '🏠'),
('Collectibles',    'collectibles',    '🏆'),
('Sports & Outdoors','sports-outdoors','⚽');

-- 5. Tabel shipping_addresses (alamat tersimpan per user)
CREATE TABLE IF NOT EXISTS shipping_addresses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  label VARCHAR(50) NOT NULL DEFAULT 'Home',
  recipient_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address TEXT NOT NULL,
  city VARCHAR(100) NOT NULL,
  province VARCHAR(100) NOT NULL,
  postal_code VARCHAR(10) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tambah kolom phone & address ke users
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER email,
  ADD COLUMN IF NOT EXISTS address TEXT NULL AFTER phone;

-- 7. Update orders lama yang statusnya 'Processing' jadi tetap valid
UPDATE orders SET status = 'Processing' WHERE status NOT IN ('Pending','Processing','Shipped','Out for Delivery','Delivered');
