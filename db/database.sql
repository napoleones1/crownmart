-- ============================================================
-- CrownMart Marketplace - Database Setup
-- Jalankan file ini di phpMyAdmin atau MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS crownmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crownmart;

-- ============================================================
-- TABEL USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL DEFAULT 'Emily Davis',
  email VARCHAR(150) NOT NULL DEFAULT 'emily.davis@example.com',
  balance DECIMAL(15,2) NOT NULL DEFAULT 5000.00,
  is_seller TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, email, balance, is_seller) VALUES
('Emily Davis', 'emily.davis@example.com', 5000.00, 0);

-- ============================================================
-- TABEL PRODUCTS
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
  id VARCHAR(50) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(15,2) NOT NULL,
  starting_price DECIMAL(15,2) DEFAULT NULL,
  category VARCHAR(100) NOT NULL,
  image VARCHAR(500) NOT NULL,
  type ENUM('fixed','auction') NOT NULL DEFAULT 'fixed',
  rating DECIMAL(3,1) NOT NULL DEFAULT 5.0,
  review_count INT NOT NULL DEFAULT 0,
  is_featured TINYINT(1) DEFAULT 0,
  stock INT DEFAULT NULL,
  shipping ENUM('free','standard') NOT NULL DEFAULT 'free',
  delivery_days INT NOT NULL DEFAULT 2,
  end_time BIGINT DEFAULT NULL,
  seller_name VARCHAR(100) NOT NULL,
  seller_rating DECIMAL(5,1) NOT NULL DEFAULT 100.0,
  seller_sales INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABEL BIDS
-- ============================================================
CREATE TABLE IF NOT EXISTS bids (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id VARCHAR(50) NOT NULL,
  bidder VARCHAR(100) NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  timestamp BIGINT NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- TABEL REVIEWS
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
  id VARCHAR(50) PRIMARY KEY,
  product_id VARCHAR(50) NOT NULL,
  user_name VARCHAR(100) NOT NULL,
  rating INT NOT NULL DEFAULT 5,
  comment TEXT NOT NULL,
  date VARCHAR(50) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- TABEL CART
-- ============================================================
CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL DEFAULT 1,
  product_id VARCHAR(50) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- TABEL ORDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
  id VARCHAR(30) PRIMARY KEY,
  user_id INT NOT NULL DEFAULT 1,
  total DECIMAL(15,2) NOT NULL,
  date_str VARCHAR(50) NOT NULL,
  status ENUM('Processing','Shipped','Out for Delivery','Delivered') DEFAULT 'Processing',
  address TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABEL ORDER ITEMS
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(30) NOT NULL,
  product_id VARCHAR(50) NOT NULL,
  quantity INT NOT NULL,
  price_at_purchase DECIMAL(15,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ============================================================
-- TABEL WATCHLIST
-- ============================================================
CREATE TABLE IF NOT EXISTS watchlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL DEFAULT 1,
  product_id VARCHAR(50) NOT NULL,
  UNIQUE KEY unique_watch (user_id, product_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- DATA AWAL PRODUK
-- ============================================================
SET @now_ms = UNIX_TIMESTAMP(NOW()) * 1000;

INSERT INTO products VALUES
('prod-1','AuraSound Max Noise-Canceling Headphones','Immerse yourself in pure high-fidelity audio with active adaptive noise cancellation, 40-hour battery life, and ultra-plush memory foam ear cups. Supports spatial audio tracking for high-end movie theater immersion.',329.99,NULL,'Electronics','https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=60','fixed',4.8,245,1,24,'free',1,NULL,'AuraSound Official',99.4,15420,NOW()),
('prod-2','Vintage 1989 GameBoy Classic (MINT Condition)','Extremely rare, untouched collectors-grade Nintendo Game Boy from the original production run in 1989. Fully inspected, clean battery contacts. Original box & Mario Land included!',430.00,150.00,'Collectibles','https://images.unsplash.com/photo-1531525645387-7f14be1bdbbd?w=600&auto=format&fit=crop&q=60','auction',4.9,12,1,NULL,'standard',3,NULL,'TimeCapsuleRetro',98.9,420,NOW()),
('prod-3','Holo Charizard 1st Edition Shadowless PSA 9 Mint','The holy grail of trading card games. 1999 Base Set Charizard 4/102 Holo Shadowless, certified by PSA with a Mint 9 grading. Pristine gloss finish and vivid rich color preservation.',12400.00,5000.00,'Collectibles','https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&auto=format&fit=crop&q=60','auction',5.0,3,1,NULL,'free',2,NULL,'EliteCardVault',100.0,189,NOW()),
('prod-4','Chronos Odyssey Automatic Sports Watch','Precision mechanical movement with 200m diver water resistance. Features a dark maritime dial, bidirectional ceramic bezel, super-luminova hands, and brushed 316L stainless steel band.',895.00,NULL,'Fashion','https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?w=600&auto=format&fit=crop&q=60','fixed',4.7,89,0,5,'free',2,NULL,'ChronosTimepieces',97.4,1205,NOW()),
('prod-5','Axiom Pro Ergonomic Mechanical Keyboard','Anodized aluminum CNC frame, customizable hot-swappable switches, sound dampening foam, and dual-mode wireless connectivity. Perfect for professionals desiring ergonomic layout.',189.99,NULL,'Electronics','https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600&auto=format&fit=crop&q=60','fixed',4.6,112,0,14,'free',1,NULL,'AxiomLabs',99.1,3410,NOW()),
('prod-6','Pro-Combat Weighted Athletic Gym Shoes','Designed for cross-fit explosive training and heavy compound powerlifting. Features an integrated heel anchor plate, active high-impact rubber soles, and breathable compression weave.',125.00,NULL,'Fashion','https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=60','fixed',4.5,310,0,45,'free',2,NULL,'ProPerformance',96.8,22100,NOW()),
('prod-7','Signed Michael Jordan 1996 Bulls Jersey','Original Chicago Bulls red away jersey signed by Michael Jordan in gold marker. Accompanied by a verifiable Upper Deck Authenticated certificate and holographic seal.',2600.00,1000.00,'Collectibles','https://images.unsplash.com/photo-1518063319789-7217e6706b04?w=600&auto=format&fit=crop&q=60','auction',4.9,8,0,NULL,'standard',4,NULL,'LegendsGrail',100.0,54,NOW()),
('prod-8','Titanium Chef Culinary Knife Set (7-Piece)','Forged from ultra-dense high-carbon VG-10 Japanese titanium clad steel. Hand-sharpened double-angle bevel, full-tang G10 composite handle, magnetic block.',249.00,NULL,'Home & Kitchen','https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=600&auto=format&fit=crop&q=60','fixed',4.9,64,0,8,'free',1,NULL,'TitaniumSteelCo',98.2,1610,NOW());

-- Set end times for auctions (relative to now)
UPDATE products SET end_time = (@now_ms + 2100000)  WHERE id = 'prod-2';
UPDATE products SET end_time = (@now_ms + 64800000) WHERE id = 'prod-3';
UPDATE products SET end_time = (@now_ms + 14400000) WHERE id = 'prod-7';

-- Seed bids
INSERT INTO bids (product_id, bidder, amount, timestamp) VALUES
('prod-2', 'GameCollector99',  300.00,   @now_ms - 14400000),
('prod-2', 'NostalgiaTripper', 380.00,   @now_ms - 7200000),
('prod-2', 'VaultKeeper',      430.00,   @now_ms - 1800000),
('prod-3', 'PalletTownBoss',   8500.00,  @now_ms - 43200000),
('prod-3', 'PikaPikaRich',     11000.00, @now_ms - 21600000),
('prod-3', 'CharizardLover',   12400.00, @now_ms - 3600000),
('prod-7', 'WindyCityBig',     1800.00,  @now_ms - 10800000),
('prod-7', 'JumpmanCollector', 2600.00,  @now_ms - 180000);

-- Seed reviews
INSERT INTO reviews VALUES
('rev-1','prod-1','Sarah M.',5,'Incredible noise-cancelling. I use them on all my flights!','May 28, 2026'),
('rev-2','prod-1','David K.',4,'Great sound quality but slightly heavy after 4 hours of use.','May 15, 2026'),
('rev-3','prod-2','RetroFanatic',5,'Seller has amazing packaging. Item arrived precisely in advertised condition.','April 10, 2026'),
('rev-4','prod-3','TCGMaster',5,'Unbelievable card. Certified scan checks out perfectly on PSA portal.','May 20, 2026'),
('rev-5','prod-4','James B.',5,'Keeps time impeccably and looks far more expensive than it is.','May 05, 2026'),
('rev-6','prod-5','Emma T.',5,'The typing feel is absolute luxury. The gold brass plate sounds marvelous.','May 12, 2026'),
('rev-7','prod-7','BullsNation',5,'Spectacular piece of history. Displayed proudly in my home bar.','April 22, 2026');

-- Seed watchlist default
INSERT INTO watchlist (user_id, product_id) VALUES (1, 'prod-2'), (1, 'prod-3');
