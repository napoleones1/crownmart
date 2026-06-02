-- ============================================================
-- CrownMart - Auth Migration
-- Jalankan di phpMyAdmin setelah database.sql sudah ada
-- ============================================================
USE crownmart;

-- Tambah kolom auth ke tabel users yang sudah ada
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS username VARCHAR(50) UNIQUE AFTER id,
  ADD COLUMN IF NOT EXISTS password VARCHAR(255) AFTER username,
  ADD COLUMN IF NOT EXISTS role ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer' AFTER is_seller,
  ADD COLUMN IF NOT EXISTS avatar VARCHAR(10) NOT NULL DEFAULT '👤' AFTER role,
  ADD COLUMN IF NOT EXISTS status ENUM('active','suspended') NOT NULL DEFAULT 'active' AFTER avatar;

-- Update user demo lama jadi buyer dengan password
UPDATE users SET
  username = 'emily',
  password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
  role     = 'buyer',
  avatar   = '👩',
  status   = 'active'
WHERE id = 1;

-- Tambah akun ADMIN
INSERT INTO users (name, email, username, password, balance, is_seller, role, avatar, status) VALUES
('Admin CrownMart', 'admin@crownmart.com', 'admin',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
 999999.00, 1, 'admin', '👑', 'active');

-- Tambah akun SELLER demo
INSERT INTO users (name, email, username, password, balance, is_seller, role, avatar, status) VALUES
('Seller Demo', 'seller@crownmart.com', 'seller',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
 2500.00, 1, 'seller', '🏪', 'active');

-- Tambah kolom user_id ke products agar produk terikat ke seller
ALTER TABLE products
  ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER id,
  ADD COLUMN IF NOT EXISTS is_approved TINYINT(1) NOT NULL DEFAULT 1 AFTER is_featured;

-- Update produk demo ke admin
UPDATE products SET user_id = 2, is_approved = 1 WHERE user_id IS NULL;
