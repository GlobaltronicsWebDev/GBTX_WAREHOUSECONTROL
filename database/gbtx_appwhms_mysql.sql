-- ========================================================
-- Globaltronics Warehouse Management System (GBTX WHMS)
-- Complete Database Dump (Schema + Active Data)
-- Generated on: 2026-11-20 00:42:00
-- Target DBMS: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `badge_color` varchar(255) NOT NULL DEFAULT 'blue',
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `roles` (4 records)
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `badge_color`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'IT Administrator', 'it-admin', 'Full administrative access to user accounts, roles, security policies, and system configs.', 'purple', 1, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(2, 'Warehouse Admin', 'warehouse-admin', 'Oversees warehouse floor operations, storage bays, dispatch queues, and inventory flows.', 'amber', 1, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(3, 'Warehouse Staff', 'warehouse-staff', 'Floor operational personnel managing item picking, pallet loading, and barcode registration.', 'emerald', 0, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(7, 'Technical', 'technical', 'Technical personnel handling Stock Requisition Forms (SRF), site installation hardware, and diagnostics.', 'cyan', 0, '2026-10-06 03:49:06', '2026-10-06 03:49:06');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users` (5 records)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Mark Joseph Estoesta', 'mark.estoesta@globaltronics.net', NULL, '$2y$12$daM8abN8XDOPx.mfO97Shu0Zt47/O1s9sgFym7ZXRLuCN/CMtwtwC', '8d5nvmhPq8ZtmbIX6rB4wwPdcuL7TKGCtSib5ksvys1kxoXHCpED7HuXfpTR', '2026-09-25 06:31:10', '2026-10-06 04:35:41'),
(2, 'Joshua Labios', 'joshua.labios@globaltronics.net', NULL, '$2y$12$PBhNwHfuSqZ.73MQrpZEneFZevL/qFZklypAFVSgpDi7jUDAIXjhq', '64m8ikmGnpucC3yZ47iM1Ym5sGszw953jFOYbUojBigefWfIB3dr4o8zhfhB', '2026-09-25 06:31:10', '2026-10-06 03:49:53'),
(3, 'Warehouse Staff User', 'staff@globaltronics.net', NULL, '$2y$12$Z2XsrlQCHWC4hc6EZu.q.ezd.2wKI9DwG8CMpkkoO9Y9Gw0Kq4Px6', 'R8lQoJMwqcxboUAZobBTeOXqupVUQTo2zpOL6ZGy50EftcDtPhqQqeAaz7gx', '2026-09-25 06:31:10', '2026-10-06 03:49:53'),
(4, 'Ariel Moro', 'ariel.moro@globaltronics.net', NULL, '$2y$12$2DwBWwV8GBSE//BmxbyYR.FDNR9cfh1ejpJSaxCMYtFB8rdXmRNhm', 'HIVKq0D3E9m37BYx1JIdaoXiExkpnhNTOMjLOHkFxJzz5BL60C9wF2hvk3ZH', '2026-10-06 03:27:48', '2026-10-06 03:49:53'),
(6, 'Charles Quiachon', 'charles.quiachon@globaltronics.net', NULL, '$2y$12$D/CMTp5fVbH3pVoTeHbcZOrbJ11pqwf/lYcf1SGGJ6LRAGN0XSHES', NULL, '2026-10-06 04:36:48', '2026-10-06 04:36:48');

-- --------------------------------------------------------
-- Table structure for table `role_user`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `role_user` (5 records)
INSERT INTO `role_user` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(2, 2, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(3, 3, '2026-10-05 05:38:09', '2026-10-05 05:38:09'),
(4, 7, '2026-10-06 03:49:07', '2026-10-06 03:49:07'),
(6, 2, '2026-10-06 04:36:48', '2026-10-06 04:36:48');

-- --------------------------------------------------------
-- Table structure for table `srf_requisitions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `srf_requisitions`;
CREATE TABLE `srf_requisitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `srf_number` varchar(255) NOT NULL,
  `sso_number` varchar(255) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `date_needed` date DEFAULT NULL,
  `requisition_date` date DEFAULT NULL,
  `item_model` varchar(255) DEFAULT NULL,
  `item_manufacturer` varchar(255) DEFAULT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `uom` varchar(50) NOT NULL DEFAULT 'PCS',
  `stock_status` varchar(255) NOT NULL DEFAULT 'not_tracked',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `department` varchar(255) DEFAULT 'TECHNICAL',
  `prepared_by` varchar(255) NOT NULL,
  `noted_by` varchar(255) DEFAULT NULL,
  `pre_approved_by` varchar(255) DEFAULT NULL,
  `approved_by` varchar(255) DEFAULT 'Macy Guido Lee',
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `verified_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `verified_by_name` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `srf_requisitions_user_id_foreign` (`user_id`),
  KEY `srf_requisitions_verified_by_user_id_foreign` (`verified_by_user_id`),
  CONSTRAINT `srf_requisitions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `srf_requisitions_verified_by_user_id_foreign` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `srf_requisitions` (2 records)
INSERT INTO `srf_requisitions` (`id`, `srf_number`, `sso_number`, `project_name`, `quantity`, `stock_status`, `status`, `prepared_by`, `user_id`, `verified_by_user_id`, `verified_by_name`, `verified_at`, `verification_notes`, `notes`, `created_at`, `updated_at`, `client`, `po_number`, `date_needed`, `requisition_date`, `uom`, `remarks`, `department`, `noted_by`, `pre_approved_by`, `approved_by`, `item_model`, `item_manufacturer`, `item_category`, `item_description`) VALUES
(3, '2026 - 0957', 'SSO-2026-0101', 'SM PRIME HOLDINGS', 1, 'available_reserved', 'on_hold', 'JOSHUA', 2, 2, 'Joshua Labios', '2026-10-07 04:14:57', 'Placed on hold pending further review.', NULL, '2026-10-07 04:04:10', '2026-10-07 04:14:57', 'SM PRIME HOLDINGS', 'PO- 2026-0123', '2026-10-10 00:00:00', '2026-10-05 00:00:00', 'PCS', 'PACKAGING', 'WAREHOUSE', 'FELIX', 'TEDDY BAJETA', 'Macy Guido Lee', 'UDT 2.5', 'UNILUMIN', 'CENTRALIZED LED INVENTORY', 'LED DISPLAY (960 X 960MM) - OUTDOOR'),
(4, '2026 - 0957', 'SSO-2026-0101', 'SM PRIME HOLDINGS', 2, 'available_reserved', 'on_hold', 'JOSHUA', 2, 2, 'Joshua Labios', '2026-10-07 04:14:57', 'Placed on hold pending further review.', NULL, '2026-10-07 04:04:10', '2026-10-07 04:14:57', 'SM PRIME HOLDINGS', 'PO- 2026-0123', '2026-10-10 00:00:00', '2026-10-05 00:00:00', 'BOXES', 'TEST', 'WAREHOUSE', 'FELIX', 'TEDDY BAJETA', 'Macy Guido Lee', 'UDAII 1.5 COB', 'UNILUMIN', 'CENTRALIZED LED INVENTORY', 'LED DISPLAY (640 X 480MM) - INDOOR');

-- --------------------------------------------------------
-- Table structure for table `transaction_notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `transaction_notifications`;
CREATE TABLE `transaction_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `actor_name` varchar(255) NOT NULL DEFAULT 'System',
  `actor_role` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'general',
  `reference_id` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_notifications_user_id_foreign` (`user_id`),
  KEY `transaction_notifications_actor_id_foreign` (`actor_id`),
  CONSTRAINT `transaction_notifications_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `transaction_notifications` (4 records)
INSERT INTO `transaction_notifications` (`id`, `user_id`, `actor_id`, `actor_name`, `actor_role`, `title`, `message`, `type`, `reference_id`, `is_read`, `created_at`, `updated_at`) VALUES
(1, NULL, 4, 'Ariel Moro', 'Technical', 'Stock Requisition Form (SRF) Submitted', 'Ariel Moro submitted SRF #2026 - 0957 with 2 item(s) (20 PCS of A0625 (R), 1 PCS of A0625) for \'SM PRIME HOLDINGS MEGAMALL\'. Status: PENDING Warehouse Review.', 'srf_submitted', '2026 - 0957', 1, '2026-10-06 06:25:34', '2026-10-06 07:06:53'),
(2, NULL, 6, 'Charles Quiachon', 'Warehouse Admin', 'SRF Verified & Completed', 'Warehouse team member Charles Quiachon verified and approved SRF #2026 - 0957 for project \'SM PRIME HOLDINGS MEGAMALL\'. Status updated to COMPLETED.', 'srf_verified', '2026 - 0957', 1, '2026-10-06 06:41:54', '2026-10-06 07:06:53'),
(3, NULL, 2, 'Joshua Labios', 'Warehouse Admin', 'Stock Requisition Form (SRF) Submitted', 'JOSHUA submitted SRF #2026 - 0957 with 2 item(s) (1 PCS of UDT 2.5, 2 BOXES of UDAII 1.5 COB) for \'SM PRIME HOLDINGS\'. Status: PENDING Warehouse Review.', 'srf_submitted', '2026 - 0957', 1, '2026-10-07 04:04:10', '2026-10-07 04:08:57'),
(4, NULL, 2, 'Joshua Labios', 'Warehouse Admin', 'SRF Placed On Hold', 'Warehouse team member Joshua Labios placed SRF #2026 - 0957 for project \'SM PRIME HOLDINGS\' ON HOLD.', 'srf_on_hold', '2026 - 0957', 0, '2026-10-07 04:14:57', '2026-10-07 04:14:57');

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for cache and locks
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for background queues and jobs
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations` (19 records)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_05_053650_create_roles_table', 2),
(5, '2026_10_05_085437_create_inventory_items_table', 3),
(6, '2026_10_05_091812_add_screen_size_to_inventory_items_table', 4),
(7, '2026_10_05_093110_add_led_fields_to_inventory_items_table', 5),
(8, '2026_10_06_043251_create_srf_requisitions_table', 6),
(9, '2026_10_06_043255_create_transaction_notifications_table', 6),
(10, '2026_10_06_050802_add_details_to_srf_requisitions_table', 7),
(11, '2026_10_06_052435_remove_unique_from_srf_number_in_srf_requisitions_table', 8),
(12, '2026_10_07_011557_add_forecasted_quantity_to_inventory_items_table', 9),
(13, '2026_10_07_012252_change_tag_number_to_text_in_inventory_items_table', 10),
(14, '2026_10_07_034401_add_signatories_and_department_to_srf_requisitions_table', 11),
(15, '2026_10_07_065057_add_acu_quantity_to_inventory_items_table', 12),
(16, '2026_10_09_055000_add_expanded_fields_to_inventory_items_table', 13),
(17, '2026_10_09_083500_change_reservation_and_history_project_to_text_in_inventory_items_table', 13),
(18, '2026_10_14_000001_add_is_admin_to_users_table', 13),
(19, '2026_11_19_100221_remove_sales_and_inventory_modules', 13);

SET FOREIGN_KEY_CHECKS = 1;
-- Export completed successfully.