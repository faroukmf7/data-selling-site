-- Migration: Add guest_transactions table for guest purchases
-- Created: 2026-01-17

CREATE TABLE IF NOT EXISTS `guest_transactions` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `guest_email` VARCHAR(100) NOT NULL,
  `guest_phone` VARCHAR(20) NOT NULL,
  `recipient_number` VARCHAR(20) NOT NULL,
  `product_id` INT,
  `amount` DECIMAL(10, 2) NOT NULL,
  `product_name` VARCHAR(255),
  `network` VARCHAR(50),
  `category` VARCHAR(50),
  `data_amount` DECIMAL(10, 2),
  `exam_type` VARCHAR(100),
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX idx_reference (reference),
  INDEX idx_guest_email (guest_email),
  INDEX idx_guest_phone (guest_phone),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
);
