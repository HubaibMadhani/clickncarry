-- Migration: add archive audit fields to products
-- Adds columns to record who archived a product and when.
-- Run this with the migration runner or via mysql CLI.

ALTER TABLE products
  ADD COLUMN deleted TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN archived_by INT NULL,
  ADD COLUMN archived_at DATETIME NULL;

-- Add indexes for faster queries on archived state and archived time
CREATE INDEX IF NOT EXISTS idx_products_deleted ON products (deleted);
CREATE INDEX IF NOT EXISTS idx_products_archived_at ON products (archived_at);
