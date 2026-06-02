-- ============================================================
-- CrownMart v3 Migration — Transfer Bank -> Card (Debit/Credit)
-- ============================================================
USE crownmart;

-- Update ENUM kolom payment_method di orders
ALTER TABLE orders MODIFY payment_method 
  ENUM('wallet','card','cod') NOT NULL DEFAULT 'wallet';

-- Update ENUM kolom method di payments
ALTER TABLE payments MODIFY method
  ENUM('wallet','card','cod') NOT NULL;

-- Update data lama: 'transfer' -> 'card'
UPDATE orders   SET payment_method = 'card' WHERE payment_method = 'transfer';
UPDATE payments SET method         = 'card' WHERE method = 'transfer';
