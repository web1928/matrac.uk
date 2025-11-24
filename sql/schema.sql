-- MatraC Database Schema - Phase 1
-- Material Traceability System
-- Database: fmyuaekbps

-- ==============================================
-- MASTER DATA TABLES
-- ==============================================

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hash',
  `email` VARCHAR(100) NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `role` VARCHAR(30) NOT NULL COMMENT 'admin, goods_receptor, goods_issuer, mixer, qa, stock_manager, manager',
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Material master table (using your existing structure)
CREATE TABLE IF NOT EXISTS `material` (
  `material_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(14) NOT NULL,
  `description` VARCHAR(100) NOT NULL,
  `base_uom` VARCHAR(6) NOT NULL COMMENT 'Base unit of measure (KG, L, EA, etc.)',
  `material_group` VARCHAR(30) NOT NULL,
  `internal_life_days` SMALLINT(5) UNSIGNED DEFAULT NULL COMMENT 'Default days for internal shelf life',
  `sieved_flag` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1=Material must be sieved at Goods Issue',
  `sieve_size` DECIMAL(3,2) DEFAULT NULL COMMENT 'Mesh size if sieved',
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`material_id`),
  UNIQUE KEY `code` (`code`),
  INDEX `idx_description` (`description`),
  INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alternative UOM table
CREATE TABLE IF NOT EXISTS `material_alt_uom` (
  `alt_uom_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `material_id` INT(6) UNSIGNED NOT NULL,
  `alt_uom` VARCHAR(6) NOT NULL COMMENT 'Alternative unit (CTN, BOX, LB, etc.)',
  `conversion_factor` DECIMAL(10,4) NOT NULL COMMENT 'How many base_uom = 1 alt_uom',
  `is_preferred_gr` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT 'Preferred for Goods Receipt',
  `is_preferred_gi` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT 'Preferred for Goods Issue',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`alt_uom_id`),
  FOREIGN KEY (`material_id`) REFERENCES `material`(`material_id`) ON DELETE CASCADE,
  UNIQUE KEY `material_alt_uom` (`material_id`, `alt_uom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier master table
CREATE TABLE IF NOT EXISTS `supplier` (
  `supplier_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_name` VARCHAR(100) NOT NULL,
  `contact_name` VARCHAR(100) DEFAULT NULL,
  `contact_email` VARCHAR(100) DEFAULT NULL,
  `contact_phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`supplier_id`),
  INDEX `idx_name` (`supplier_name`),
  INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Process stages
CREATE TABLE IF NOT EXISTS `stage` (
  `stage_id` INT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stage_code` VARCHAR(20) NOT NULL,
  `stage_name` VARCHAR(50) NOT NULL,
  `display_order` INT(3) UNSIGNED NOT NULL,
  PRIMARY KEY (`stage_id`),
  UNIQUE KEY `stage_code` (`stage_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert standard stages
INSERT INTO `stage` (`stage_code`, `stage_name`, `display_order`) VALUES
('GR', 'Goods Receipt', 1),
('DEBOX', 'Deboxing/Tempering', 2),
('GI', 'Goods Issue', 3),
('MIX', 'Mixing', 4),
('REWORK', 'Rework Available', 5);

-- Hold status
CREATE TABLE IF NOT EXISTS `hold_status` (
  `hold_status_id` INT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_code` VARCHAR(20) NOT NULL,
  `status_name` VARCHAR(50) NOT NULL,
  `is_available` TINYINT(1) UNSIGNED NOT NULL COMMENT '1=Available for next stage, 0=Blocked',
  PRIMARY KEY (`hold_status_id`),
  UNIQUE KEY `status_code` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert standard hold statuses
INSERT INTO `hold_status` (`status_code`, `status_name`, `is_available`) VALUES
('AVAILABLE', 'Available', 1),
('ON_HOLD', 'On Hold', 0),
('REJECTED', 'Rejected', 0);

-- ==============================================
-- TRANSACTIONAL TABLES
-- ==============================================

-- Batch records (receipts)
CREATE TABLE IF NOT EXISTS `batch` (
  `batch_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `internal_batch_code` VARCHAR(15) NOT NULL COMMENT 'Format: yy-jjj-nnn',
  `material_id` INT(6) UNSIGNED NOT NULL,
  `supplier_id` INT(6) UNSIGNED DEFAULT NULL,
  `supplier_useby_1` DATE DEFAULT NULL,
  `supplier_batch_code_1` VARCHAR(50) DEFAULT NULL,
  `supplier_useby_2` DATE DEFAULT NULL,
  `supplier_batch_code_2` VARCHAR(50) DEFAULT NULL,
  `supplier_useby_3` DATE DEFAULT NULL,
  `supplier_batch_code_3` VARCHAR(50) DEFAULT NULL,
  `supplier_useby_4` DATE DEFAULT NULL,
  `supplier_batch_code_4` VARCHAR(50) DEFAULT NULL,
  `delivered_quantity` DECIMAL(10,3) NOT NULL,
  `delivered_qty_uom` VARCHAR(6) NOT NULL,
  `receipt_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `po_number` VARCHAR(50) DEFAULT NULL,
  `haulier_name` VARCHAR(100) DEFAULT NULL,
  `delivery_note_ref` VARCHAR(50) DEFAULT NULL,
  `user_id` INT(6) UNSIGNED NOT NULL COMMENT 'User who created receipt',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`batch_id`),
  UNIQUE KEY `internal_batch_code` (`internal_batch_code`),
  FOREIGN KEY (`material_id`) REFERENCES `material`(`material_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `supplier`(`supplier_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`),
  INDEX `idx_receipt_date` (`receipt_date`),
  INDEX `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory table
CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_id` INT(10) UNSIGNED NOT NULL,
  `stage_id` INT(3) UNSIGNED NOT NULL,
  `hold_status_id` INT(3) UNSIGNED NOT NULL DEFAULT 1,
  `quantity` DECIMAL(10,3) NOT NULL COMMENT 'Always in base UOM',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inventory_id`),
  FOREIGN KEY (`batch_id`) REFERENCES `batch`(`batch_id`),
  FOREIGN KEY (`stage_id`) REFERENCES `stage`(`stage_id`),
  FOREIGN KEY (`hold_status_id`) REFERENCES `hold_status`(`hold_status_id`),
  INDEX `idx_batch_stage` (`batch_id`, `stage_id`),
  INDEX `idx_quantity` (`quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions (unified audit trail)
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_type` VARCHAR(20) NOT NULL COMMENT 'GR, DEBOX, GI, MIX, HOLD, ADJUST',
  `batch_id` INT(10) UNSIGNED NOT NULL,
  `from_stage_id` INT(3) UNSIGNED DEFAULT NULL,
  `to_stage_id` INT(3) UNSIGNED DEFAULT NULL,
  `quantity` DECIMAL(10,3) NOT NULL COMMENT 'In base UOM',
  `user_id` INT(6) UNSIGNED NOT NULL,
  `additional_data` JSON DEFAULT NULL COMMENT 'Stage-specific fields (sieving, etc.)',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  FOREIGN KEY (`batch_id`) REFERENCES `batch`(`batch_id`),
  FOREIGN KEY (`from_stage_id`) REFERENCES `stage`(`stage_id`),
  FOREIGN KEY (`to_stage_id`) REFERENCES `stage`(`stage_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`),
  INDEX `idx_type` (`transaction_type`),
  INDEX `idx_batch` (`batch_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batch relationships (parent/child traceability)
CREATE TABLE IF NOT EXISTS `batch_relationships` (
  `relationship_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_batch_id` INT(10) UNSIGNED NOT NULL COMMENT 'Ingredient batch',
  `child_batch_id` INT(10) UNSIGNED NOT NULL COMMENT 'Output batch (dough, etc.)',
  `quantity_consumed` DECIMAL(10,3) NOT NULL COMMENT 'How much of parent went into child',
  `transaction_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'Link to mix transaction',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`relationship_id`),
  FOREIGN KEY (`parent_batch_id`) REFERENCES `batch`(`batch_id`),
  FOREIGN KEY (`child_batch_id`) REFERENCES `batch`(`batch_id`),
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`transaction_id`),
  INDEX `idx_parent` (`parent_batch_id`),
  INDEX `idx_child` (`child_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- TEST DATA (Optional - Remove for production)
-- ==============================================

-- Insert test user (password: admin123 - bcrypt hash)
-- Note: In Phase 2, use password_hash() in PHP
INSERT INTO `users` (`username`, `password_hash`, `email`, `first_name`, `last_name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@matrac.uk', 'Admin', 'User', 'admin');

-- Insert test materials
INSERT INTO `material` (`code`, `description`, `base_uom`, `material_group`, `sieved_flag`) VALUES
('FLOUR-001', 'Strong White Flour', 'KG', 'Flour', 1),
('BUTTER-001', 'Unsalted Butter', 'KG', 'Dairy', 0),
('YEAST-001', 'Fresh Yeast', 'KG', 'Leavening', 0);

-- Insert test supplier
INSERT INTO `supplier` (`supplier_name`, `contact_name`, `contact_email`) VALUES
('ABC Milling Co', 'John Smith', 'john@abcmilling.com'),
('Dairy Direct Ltd', 'Jane Doe', 'jane@dairydirect.com');
