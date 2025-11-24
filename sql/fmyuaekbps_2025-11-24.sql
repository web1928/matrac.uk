# ************************************************************
# Sequel Ace SQL dump
# Version 20095
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: localhost (MySQL 12.0.2-MariaDB)
# Database: fmyuaekbps
# Generation Time: 2025-11-24 08:28:39 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table batch
# ------------------------------------------------------------

DROP TABLE IF EXISTS `batch`;

CREATE TABLE `batch` (
  `batch_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `internal_batch_code` varchar(15) NOT NULL COMMENT 'Format: yy-jjj-nnn',
  `material_id` int(6) unsigned NOT NULL,
  `supplier_id` int(6) unsigned DEFAULT NULL,
  `supplier_useby_1` date DEFAULT NULL,
  `supplier_batch_code_1` varchar(50) DEFAULT NULL,
  `supplier_useby_2` date DEFAULT NULL,
  `supplier_batch_code_2` varchar(50) DEFAULT NULL,
  `supplier_useby_3` date DEFAULT NULL,
  `supplier_batch_code_3` varchar(50) DEFAULT NULL,
  `supplier_useby_4` date DEFAULT NULL,
  `supplier_batch_code_4` varchar(50) DEFAULT NULL,
  `delivered_quantity` decimal(10,3) NOT NULL,
  `delivered_qty_uom` varchar(6) NOT NULL,
  `receipt_date` datetime NOT NULL DEFAULT current_timestamp(),
  `po_number` varchar(50) DEFAULT NULL,
  `haulier_name` varchar(100) DEFAULT NULL,
  `delivery_note_ref` varchar(50) DEFAULT NULL,
  `silo_no` varchar(10) DEFAULT NULL COMMENT 'Silo assignment (S1/S2/S3/S1 & S3)',
  `coc_coa_attached` varchar(3) DEFAULT NULL COMMENT 'Certificate attached? (Yes/No)',
  `rma_sheet_completed` varchar(3) DEFAULT NULL COMMENT 'RMA sheet attached & completed? (Yes/No)',
  `matches_delivery_note` varchar(3) DEFAULT NULL COMMENT 'Matches delivery note? (Yes/No)',
  `bookin_confirmation_no` varchar(50) DEFAULT NULL COMMENT 'Booked-in confirmation number',
  `receipt_comments` text DEFAULT NULL COMMENT 'Additional receipt comments',
  `user_id` int(6) unsigned NOT NULL COMMENT 'User who created receipt',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`batch_id`),
  UNIQUE KEY `internal_batch_code` (`internal_batch_code`),
  KEY `supplier_id` (`supplier_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_receipt_date` (`receipt_date`),
  KEY `idx_material` (`material_id`),
  CONSTRAINT `batch_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `material` (`material_id`),
  CONSTRAINT `batch_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`),
  CONSTRAINT `batch_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `batch` WRITE;
/*!40000 ALTER TABLE `batch` DISABLE KEYS */;

INSERT INTO `batch` (`batch_id`, `internal_batch_code`, `material_id`, `supplier_id`, `supplier_useby_1`, `supplier_batch_code_1`, `supplier_useby_2`, `supplier_batch_code_2`, `supplier_useby_3`, `supplier_batch_code_3`, `supplier_useby_4`, `supplier_batch_code_4`, `delivered_quantity`, `delivered_qty_uom`, `receipt_date`, `po_number`, `haulier_name`, `delivery_note_ref`, `silo_no`, `coc_coa_attached`, `rma_sheet_completed`, `matches_delivery_note`, `bookin_confirmation_no`, `receipt_comments`, `user_id`, `created_at`)
VALUES
	(5,'25-320-001',1,1,'2026-02-13','batch25',NULL,NULL,NULL,NULL,NULL,NULL,25.000,'KG','2025-11-16 17:56:45','BUK30000234567','Oceans01','DelRef001',NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-16 17:56:45'),
	(6,'25-321-001',3,1,'2025-11-20','batch26',NULL,NULL,NULL,NULL,NULL,NULL,100.000,'EA','2025-11-17 14:30:09','BUK30000234568','Oceans02','DelRef002',NULL,NULL,NULL,NULL,NULL,'Some comments',1,'2025-11-17 14:30:09'),
	(7,'25-327-001',2,2,'2025-12-25','Batch251225',NULL,NULL,NULL,NULL,NULL,NULL,188.000,'KG','2025-11-23 09:12:23','BUK30000111111','Oceans01','DelNote001',NULL,NULL,NULL,NULL,'ConfNum001','My comments are so good 001',2,'2025-11-23 09:12:23'),
	(8,'25-327-002',1,1,'2025-12-26','Batch251226','2025-12-27','Batch2002',NULL,NULL,NULL,NULL,189.000,'KG','2025-11-23 09:13:46',NULL,NULL,NULL,'S1',NULL,NULL,NULL,'ConfNum002','More excellent comments 002',2,'2025-11-23 09:13:46'),
	(9,'25-327-003',2,2,'2026-01-01','batch27',NULL,NULL,NULL,NULL,NULL,NULL,500.000,'KG','2025-11-23 19:44:00','BUK30000234569','Oceans03','DelRef003',NULL,NULL,NULL,NULL,NULL,'More comments',1,'2025-11-23 19:44:00');

/*!40000 ALTER TABLE `batch` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table batch_relationships
# ------------------------------------------------------------

DROP TABLE IF EXISTS `batch_relationships`;

CREATE TABLE `batch_relationships` (
  `relationship_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_batch_id` int(10) unsigned NOT NULL COMMENT 'Ingredient batch',
  `child_batch_id` int(10) unsigned NOT NULL COMMENT 'Output batch (dough, etc.)',
  `quantity_consumed` decimal(10,3) NOT NULL COMMENT 'How much of parent went into child',
  `transaction_id` int(10) unsigned DEFAULT NULL COMMENT 'Link to mix transaction',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`relationship_id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `idx_parent` (`parent_batch_id`),
  KEY `idx_child` (`child_batch_id`),
  CONSTRAINT `batch_relationships_ibfk_1` FOREIGN KEY (`parent_batch_id`) REFERENCES `batch` (`batch_id`),
  CONSTRAINT `batch_relationships_ibfk_2` FOREIGN KEY (`child_batch_id`) REFERENCES `batch` (`batch_id`),
  CONSTRAINT `batch_relationships_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table hold_status
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hold_status`;

CREATE TABLE `hold_status` (
  `hold_status_id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `status_code` varchar(20) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `is_available` tinyint(1) unsigned NOT NULL COMMENT '1=Available for next stage, 0=Blocked',
  PRIMARY KEY (`hold_status_id`),
  UNIQUE KEY `status_code` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `hold_status` WRITE;
/*!40000 ALTER TABLE `hold_status` DISABLE KEYS */;

INSERT INTO `hold_status` (`hold_status_id`, `status_code`, `status_name`, `is_available`)
VALUES
	(1,'AVAILABLE','Available',1),
	(2,'ON_HOLD','On Hold',0),
	(3,'REJECTED','Rejected',0);

/*!40000 ALTER TABLE `hold_status` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table inventory
# ------------------------------------------------------------

DROP TABLE IF EXISTS `inventory`;

CREATE TABLE `inventory` (
  `inventory_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(10) unsigned NOT NULL,
  `stage_id` int(3) unsigned NOT NULL,
  `hold_status_id` int(3) unsigned NOT NULL DEFAULT 1,
  `quantity` decimal(10,3) NOT NULL COMMENT 'Always in base UOM',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`inventory_id`),
  KEY `stage_id` (`stage_id`),
  KEY `hold_status_id` (`hold_status_id`),
  KEY `idx_batch_stage` (`batch_id`,`stage_id`),
  KEY `idx_quantity` (`quantity`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batch` (`batch_id`),
  CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`stage_id`) REFERENCES `stage` (`stage_id`),
  CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`hold_status_id`) REFERENCES `hold_status` (`hold_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;

INSERT INTO `inventory` (`inventory_id`, `batch_id`, `stage_id`, `hold_status_id`, `quantity`, `created_at`, `updated_at`)
VALUES
	(1,5,1,1,25.000,'2025-11-16 17:56:45','2025-11-23 10:27:47'),
	(2,6,1,1,100.000,'2025-11-17 14:30:09','2025-11-23 10:27:22'),
	(4,7,1,1,100.000,'2025-11-23 09:12:23','2025-11-23 10:28:26'),
	(5,8,1,1,189.000,'2025-11-23 09:13:46','2025-11-23 20:09:12'),
	(7,7,1,3,88.000,'2025-11-23 10:28:26','2025-11-23 10:28:38'),
	(9,9,1,1,350.000,'2025-11-23 19:44:00','2025-11-23 20:09:50'),
	(10,9,1,3,150.000,'2025-11-23 19:44:18','2025-11-23 19:44:59');

/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table material
# ------------------------------------------------------------

DROP TABLE IF EXISTS `material`;

CREATE TABLE `material` (
  `material_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(14) NOT NULL,
  `description` varchar(100) NOT NULL,
  `base_uom` varchar(6) NOT NULL COMMENT 'Base unit of measure (KG, L, EA, etc.)',
  `material_group` varchar(30) NOT NULL,
  `internal_life_days` smallint(5) unsigned DEFAULT NULL COMMENT 'Default days for internal shelf life',
  `sieved_flag` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '1=Material must be sieved at Goods Issue',
  `sieve_size` decimal(3,2) DEFAULT NULL COMMENT 'Mesh size if sieved',
  `silo_material` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`material_id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_description` (`description`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `material` WRITE;
/*!40000 ALTER TABLE `material` DISABLE KEYS */;

INSERT INTO `material` (`material_id`, `code`, `description`, `base_uom`, `material_group`, `internal_life_days`, `sieved_flag`, `sieve_size`, `silo_material`, `active`, `created_at`, `updated_at`)
VALUES
	(1,'FLOUR-001','Strong White Flour','KG','Flour',30,1,NULL,1,1,'2025-11-16 13:41:26','2025-11-16 19:10:53'),
	(2,'BUTTER-001','Unsalted Butter','KG','Dairy',30,0,NULL,0,1,'2025-11-16 13:41:26','2025-11-16 19:10:55'),
	(3,'YEAST-001','Fresh Yeast','EA','Leavening',30,0,NULL,0,1,'2025-11-16 13:41:26','2025-11-16 19:10:56');

/*!40000 ALTER TABLE `material` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table material_alt_uom
# ------------------------------------------------------------

DROP TABLE IF EXISTS `material_alt_uom`;

CREATE TABLE `material_alt_uom` (
  `alt_uom_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int(6) unsigned NOT NULL,
  `alt_uom` varchar(6) NOT NULL COMMENT 'Alternative unit (CTN, BOX, LB, etc.)',
  `conversion_factor` decimal(10,4) NOT NULL COMMENT 'How many base_uom = 1 alt_uom',
  `is_preferred_gr` tinyint(1) unsigned DEFAULT 0 COMMENT 'Preferred for Goods Receipt',
  `is_preferred_gi` tinyint(1) unsigned DEFAULT 0 COMMENT 'Preferred for Goods Issue',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`alt_uom_id`),
  UNIQUE KEY `material_alt_uom` (`material_id`,`alt_uom`),
  CONSTRAINT `material_alt_uom_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `material` (`material_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table stage
# ------------------------------------------------------------

DROP TABLE IF EXISTS `stage`;

CREATE TABLE `stage` (
  `stage_id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `stage_code` varchar(20) NOT NULL,
  `stage_name` varchar(50) NOT NULL,
  `display_order` int(3) unsigned NOT NULL,
  PRIMARY KEY (`stage_id`),
  UNIQUE KEY `stage_code` (`stage_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `stage` WRITE;
/*!40000 ALTER TABLE `stage` DISABLE KEYS */;

INSERT INTO `stage` (`stage_id`, `stage_code`, `stage_name`, `display_order`)
VALUES
	(1,'GR','Goods Receipt',1),
	(2,'DEBOX','Deboxing/Tempering',2),
	(3,'GI','Goods Issue',3),
	(4,'MIX','Mixing',4),
	(5,'REWORK','Rework Available',5);

/*!40000 ALTER TABLE `stage` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table supplier
# ------------------------------------------------------------

DROP TABLE IF EXISTS `supplier`;

CREATE TABLE `supplier` (
  `supplier_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`supplier_id`),
  KEY `idx_name` (`supplier_name`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;

INSERT INTO `supplier` (`supplier_id`, `supplier_name`, `contact_name`, `contact_email`, `contact_phone`, `address`, `active`, `created_at`, `updated_at`)
VALUES
	(1,'ABC Milling Co','John Smith','john@abcmilling.com',NULL,NULL,1,'2025-11-16 13:41:26','2025-11-16 13:41:26'),
	(2,'Dairy Direct Ltd','Jane Doe','jane@dairydirect.com',NULL,NULL,1,'2025-11-16 13:41:26','2025-11-16 13:41:26');

/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table transactions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `transactions`;

CREATE TABLE `transactions` (
  `transaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(20) NOT NULL COMMENT 'GR, DEBOX, GI, MIX, HOLD, ADJUST',
  `batch_id` int(10) unsigned NOT NULL,
  `from_stage_id` int(3) unsigned DEFAULT NULL,
  `to_stage_id` int(3) unsigned DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL COMMENT 'In base UOM',
  `user_id` int(6) unsigned NOT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Stage-specific fields (sieving, etc.)' CHECK (json_valid(`additional_data`)),
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `from_stage_id` (`from_stage_id`),
  KEY `to_stage_id` (`to_stage_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_batch` (`batch_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batch` (`batch_id`),
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`from_stage_id`) REFERENCES `stage` (`stage_id`),
  CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`to_stage_id`) REFERENCES `stage` (`stage_id`),
  CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;

INSERT INTO `transactions` (`transaction_id`, `transaction_type`, `batch_id`, `from_stage_id`, `to_stage_id`, `quantity`, `user_id`, `additional_data`, `notes`, `created_at`)
VALUES
	(1,'GR',5,NULL,1,25.000,1,NULL,'Goods receipt','2025-11-16 17:56:45'),
	(2,'GR',6,NULL,1,100.000,1,NULL,'Goods receipt','2025-11-17 14:30:09'),
	(3,'HOLD',6,1,NULL,50.000,1,NULL,'QA action: hold','2025-11-18 17:17:46'),
	(4,'GR',7,NULL,1,188.000,2,NULL,'Goods receipt','2025-11-23 09:12:23'),
	(5,'GR',8,NULL,1,189.000,2,NULL,'Goods receipt','2025-11-23 09:13:46'),
	(6,'HOLD',7,1,NULL,88.000,1,NULL,'Has Mould','2025-11-23 10:14:45'),
	(7,'HOLD',5,1,NULL,25.000,1,NULL,'Testing fully qty hold','2025-11-23 10:16:21'),
	(8,'RELEASE',6,1,NULL,50.000,1,NULL,'it\'s all good now','2025-11-23 10:16:46'),
	(9,'HOLD',6,1,NULL,50.000,1,NULL,'testing','2025-11-23 10:27:11'),
	(10,'RELEASE',6,1,NULL,50.000,1,NULL,'Testing release','2025-11-23 10:27:22'),
	(11,'RELEASE',7,1,NULL,88.000,1,NULL,'releasing','2025-11-23 10:27:34'),
	(12,'RELEASE',5,1,NULL,25.000,1,NULL,'Releasing','2025-11-23 10:27:47'),
	(13,'HOLD',7,1,NULL,88.000,1,NULL,'testing hold','2025-11-23 10:28:26'),
	(14,'REJECT',7,1,NULL,88.000,1,NULL,'returned to supplier','2025-11-23 10:28:38'),
	(15,'HOLD',8,1,NULL,89.000,1,NULL,'Hold check','2025-11-23 19:42:52'),
	(16,'GR',9,NULL,1,500.000,1,NULL,'Goods receipt','2025-11-23 19:44:00'),
	(17,'HOLD',9,1,NULL,150.000,1,NULL,'QA action: hold','2025-11-23 19:44:18'),
	(18,'REJECT',9,1,NULL,150.000,1,NULL,'REJECTED','2025-11-23 19:44:59'),
	(19,'RELEASE',8,1,NULL,89.000,1,NULL,'QA action: release','2025-11-23 20:06:12'),
	(20,'HOLD',8,1,NULL,89.000,1,NULL,'QA action: hold','2025-11-23 20:09:07'),
	(21,'RELEASE',8,1,NULL,89.000,1,NULL,'QA action: release','2025-11-23 20:09:12'),
	(22,'HOLD',9,1,NULL,50.000,1,NULL,'QA action: hold','2025-11-23 20:09:37'),
	(23,'RELEASE',9,1,NULL,50.000,1,NULL,'QA action: release','2025-11-23 20:09:50');

/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'Bcrypt hash',
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` varchar(30) NOT NULL COMMENT 'admin, goods_receptor, goods_issuer, mixer, qa, stock_manager, manager',
  `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `first_name`, `last_name`, `role`, `active`, `last_login`, `created_at`, `updated_at`)
VALUES
	(1,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin@matrac.uk','Danny','Mason','admin',1,NULL,'2025-11-16 13:41:26','2025-11-22 20:42:49'),
	(2,'receptor','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','receptor@matrac.uk','John','Receptor','goods_receptor',1,NULL,'2025-11-16 17:54:28','2025-11-16 17:54:28'),
	(3,'issuer','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','issuer@matrac.uk','Jane','Issuer','goods_issuer',1,NULL,'2025-11-16 17:54:28','2025-11-16 17:54:28'),
	(4,'mixer','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','mixer@matrac.uk','Mike','Mixer','mixer',1,NULL,'2025-11-16 17:54:28','2025-11-16 17:54:28');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
