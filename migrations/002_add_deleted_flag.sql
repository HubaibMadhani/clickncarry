-- Migration: add deleted flag to products for soft-delete/archive behavior
ALTER TABLE products
  ADD COLUMN deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER images;

-- Optional: add an index for faster queries on non-deleted products
ALTER TABLE products
  ADD INDEX idx_products_deleted (deleted);
