<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportDatabaseSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-sql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the full GBTX WHMS database schema and seed data to standalone MySQL and SQLite SQL files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting database export for GBTX Warehouse Management System...');

        $mysqlFile = database_path('gbtx_appwhms_mysql.sql');
        $sqliteFile = database_path('gbtx_appwhms_sqlite.sql');

        $mysqlContent = $this->generateMySqlExport();
        File::put($mysqlFile, $mysqlContent);
        $this->info("MySQL export generated successfully: {$mysqlFile} (".number_format(strlen($mysqlContent)).' bytes)');

        $sqliteContent = $this->generateSqliteExport();
        File::put($sqliteFile, $sqliteContent);
        $this->info("SQLite export generated successfully: {$sqliteFile} (".number_format(strlen($sqliteContent)).' bytes)');

        $this->newLine();
        $this->info('Database export complete! Files are saved in the database/ directory.');

        return Command::SUCCESS;
    }

    /**
     * Generate complete MySQL dump.
     */
    protected function generateMySqlExport(): string
    {
        $now = now()->toDateTimeString();
        $sql = [];

        $sql[] = '-- ========================================================';
        $sql[] = '-- Globaltronics Warehouse Management System (GBTX WHMS)';
        $sql[] = '-- Complete Database Dump (Schema + Active Data)';
        $sql[] = "-- Generated on: {$now}";
        $sql[] = '-- Target DBMS: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+';
        $sql[] = '-- ========================================================';
        $sql[] = '';
        $sql[] = 'SET NAMES utf8mb4;';
        $sql[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $sql[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
        $sql[] = "SET time_zone = '+00:00';";
        $sql[] = '';

        // 1. Roles table
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `roles`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `roles`;';
        $sql[] = 'CREATE TABLE `roles` (';
        $sql[] = '  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = '  `name` varchar(255) NOT NULL,';
        $sql[] = '  `slug` varchar(255) NOT NULL,';
        $sql[] = '  `description` text DEFAULT NULL,';
        $sql[] = "  `badge_color` varchar(255) NOT NULL DEFAULT 'blue',";
        $sql[] = '  `is_system` tinyint(1) NOT NULL DEFAULT 0,';
        $sql[] = '  `created_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `updated_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  UNIQUE KEY `roles_slug_unique` (`slug`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = $this->buildInsertStatements('roles');
        $sql[] = '';

        // 2. Users table
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `users`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `users`;';
        $sql[] = 'CREATE TABLE `users` (';
        $sql[] = '  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = '  `name` varchar(255) NOT NULL,';
        $sql[] = '  `email` varchar(255) NOT NULL,';
        $sql[] = '  `email_verified_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `password` varchar(255) NOT NULL,';
        $sql[] = '  `remember_token` varchar(100) DEFAULT NULL,';
        $sql[] = '  `created_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `updated_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  UNIQUE KEY `users_email_unique` (`email`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = $this->buildInsertStatements('users');
        $sql[] = '';

        // 3. Role User pivot table
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `role_user`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `role_user`;';
        $sql[] = 'CREATE TABLE `role_user` (';
        $sql[] = '  `user_id` bigint(20) unsigned NOT NULL,';
        $sql[] = '  `role_id` bigint(20) unsigned NOT NULL,';
        $sql[] = '  `created_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `updated_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`user_id`,`role_id`),';
        $sql[] = '  KEY `role_user_role_id_foreign` (`role_id`),';
        $sql[] = '  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,';
        $sql[] = '  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = $this->buildInsertStatements('role_user');
        $sql[] = '';

        // 4. Inventory items table
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `inventory_items`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `inventory_items`;';
        $sql[] = 'CREATE TABLE `inventory_items` (';
        $sql[] = '  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = "  `category` varchar(255) NOT NULL DEFAULT 'EOL PHILIPS UNITS',";
        $sql[] = '  `tag_number` text DEFAULT NULL,';
        $sql[] = '  `manufacturer` varchar(255) NOT NULL,';
        $sql[] = '  `check_in_date` date NOT NULL,';
        $sql[] = '  `po_number` varchar(255) DEFAULT NULL,';
        $sql[] = '  `model` varchar(255) NOT NULL,';
        $sql[] = '  `screen_size` varchar(255) DEFAULT NULL,';
        $sql[] = '  `item_description` text NOT NULL,';
        $sql[] = '  `quantity` int(11) NOT NULL DEFAULT 1,';
        $sql[] = '  `forecasted_quantity` int(11) DEFAULT NULL,';
        $sql[] = '  `acu_quantity` int(11) DEFAULT NULL,';
        $sql[] = '  `sqm` decimal(10,2) DEFAULT NULL,';
        $sql[] = '  `location` varchar(255) DEFAULT NULL,';
        $sql[] = "  `status` varchar(255) NOT NULL DEFAULT 'in_stock',";
        $sql[] = '  `created_by` bigint(20) unsigned DEFAULT NULL,';
        $sql[] = '  `created_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `updated_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  KEY `inventory_items_category_index` (`category`),';
        $sql[] = '  KEY `inventory_items_manufacturer_index` (`manufacturer`),';
        $sql[] = '  KEY `inventory_items_check_in_date_index` (`check_in_date`),';
        $sql[] = '  KEY `inventory_items_model_index` (`model`),';
        $sql[] = '  KEY `inventory_items_screen_size_index` (`screen_size`),';
        $sql[] = '  KEY `inventory_items_status_index` (`status`),';
        $sql[] = '  KEY `inventory_items_created_by_foreign` (`created_by`),';
        $sql[] = '  CONSTRAINT `inventory_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = $this->buildInsertStatements('inventory_items');
        $sql[] = '';

        // 5. SRF Requisitions table
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `srf_requisitions`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `srf_requisitions`;';
        $sql[] = 'CREATE TABLE `srf_requisitions` (';
        $sql[] = '  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = '  `srf_number` varchar(255) NOT NULL,';
        $sql[] = '  `sso_number` varchar(255) NOT NULL,';
        $sql[] = '  `project_name` varchar(255) NOT NULL,';
        $sql[] = '  `client` varchar(255) DEFAULT NULL,';
        $sql[] = '  `po_number` varchar(255) DEFAULT NULL,';
        $sql[] = '  `date_needed` date DEFAULT NULL,';
        $sql[] = '  `requisition_date` date DEFAULT NULL,';
        $sql[] = '  `inventory_item_id` bigint(20) unsigned NOT NULL,';
        $sql[] = '  `quantity` int(11) NOT NULL,';
        $sql[] = "  `uom` varchar(50) NOT NULL DEFAULT 'PCS',";
        $sql[] = "  `stock_status` varchar(255) NOT NULL DEFAULT 'available_reserved',";
        $sql[] = "  `status` varchar(255) NOT NULL DEFAULT 'pending',";
        $sql[] = "  `department` varchar(255) DEFAULT 'TECHNICAL',";
        $sql[] = '  `prepared_by` varchar(255) NOT NULL,';
        $sql[] = '  `noted_by` varchar(255) DEFAULT NULL,';
        $sql[] = '  `pre_approved_by` varchar(255) DEFAULT NULL,';
        $sql[] = "  `approved_by` varchar(255) DEFAULT 'Macy Guido Lee',";
        $sql[] = '  `user_id` bigint(20) unsigned DEFAULT NULL,';
        $sql[] = '  `verified_by_user_id` bigint(20) unsigned DEFAULT NULL,';
        $sql[] = '  `verified_by_name` varchar(255) DEFAULT NULL,';
        $sql[] = '  `verified_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `verification_notes` text DEFAULT NULL,';
        $sql[] = '  `notes` text DEFAULT NULL,';
        $sql[] = '  `remarks` text DEFAULT NULL,';
        $sql[] = '  `created_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `updated_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  KEY `srf_requisitions_inventory_item_id_foreign` (`inventory_item_id`),';
        $sql[] = '  KEY `srf_requisitions_user_id_foreign` (`user_id`),';
        $sql[] = '  KEY `srf_requisitions_verified_by_user_id_foreign` (`verified_by_user_id`),';
        $sql[] = '  CONSTRAINT `srf_requisitions_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,';
        $sql[] = '  CONSTRAINT `srf_requisitions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,';
        $sql[] = '  CONSTRAINT `srf_requisitions_verified_by_user_id_foreign` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = $this->buildInsertStatements('srf_requisitions');
        $sql[] = '';

        // 6. Transaction Notifications table
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `transaction_notifications`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `transaction_notifications`;';
        $sql[] = 'CREATE TABLE `transaction_notifications` (';
        $sql[] = '  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = '  `user_id` bigint(20) unsigned DEFAULT NULL,';
        $sql[] = '  `actor_id` bigint(20) unsigned DEFAULT NULL,';
        $sql[] = "  `actor_name` varchar(255) NOT NULL DEFAULT 'System',";
        $sql[] = '  `actor_role` varchar(255) DEFAULT NULL,';
        $sql[] = '  `title` varchar(255) NOT NULL,';
        $sql[] = '  `message` text NOT NULL,';
        $sql[] = "  `type` varchar(255) NOT NULL DEFAULT 'general',";
        $sql[] = '  `reference_id` varchar(255) DEFAULT NULL,';
        $sql[] = '  `is_read` tinyint(1) NOT NULL DEFAULT 0,';
        $sql[] = '  `created_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  `updated_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  KEY `transaction_notifications_user_id_foreign` (`user_id`),';
        $sql[] = '  KEY `transaction_notifications_actor_id_foreign` (`actor_id`),';
        $sql[] = '  CONSTRAINT `transaction_notifications_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,';
        $sql[] = '  CONSTRAINT `transaction_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = $this->buildInsertStatements('transaction_notifications');
        $sql[] = '';

        // 7. Password Reset Tokens
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `password_reset_tokens`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `password_reset_tokens`;';
        $sql[] = 'CREATE TABLE `password_reset_tokens` (';
        $sql[] = '  `email` varchar(255) NOT NULL,';
        $sql[] = '  `token` varchar(255) NOT NULL,';
        $sql[] = '  `created_at` timestamp NULL DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`email`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';

        // 8. Sessions
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `sessions`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `sessions`;';
        $sql[] = 'CREATE TABLE `sessions` (';
        $sql[] = '  `id` varchar(255) NOT NULL,';
        $sql[] = '  `user_id` bigint(20) unsigned DEFAULT NULL,';
        $sql[] = '  `ip_address` varchar(45) DEFAULT NULL,';
        $sql[] = '  `user_agent` text DEFAULT NULL,';
        $sql[] = '  `payload` longtext NOT NULL,';
        $sql[] = '  `last_activity` int(11) NOT NULL,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  KEY `sessions_user_id_index` (`user_id`),';
        $sql[] = '  KEY `sessions_last_activity_index` (`last_activity`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';

        // 9. Cache & Cache Locks
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for cache and locks';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `cache`;';
        $sql[] = 'CREATE TABLE `cache` (';
        $sql[] = '  `key` varchar(255) NOT NULL,';
        $sql[] = '  `value` mediumtext NOT NULL,';
        $sql[] = '  `expiration` bigint(20) NOT NULL,';
        $sql[] = '  PRIMARY KEY (`key`),';
        $sql[] = '  KEY `cache_expiration_index` (`expiration`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = 'DROP TABLE IF EXISTS `cache_locks`;';
        $sql[] = 'CREATE TABLE `cache_locks` (';
        $sql[] = '  `key` varchar(255) NOT NULL,';
        $sql[] = '  `owner` varchar(255) NOT NULL,';
        $sql[] = '  `expiration` bigint(20) NOT NULL,';
        $sql[] = '  PRIMARY KEY (`key`),';
        $sql[] = '  KEY `cache_locks_expiration_index` (`expiration`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';

        // 10. Jobs, Job Batches, Failed Jobs
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for background queues and jobs';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `jobs`;';
        $sql[] = 'CREATE TABLE `jobs` (';
        $sql[] = '  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = '  `queue` varchar(255) NOT NULL,';
        $sql[] = '  `payload` longtext NOT NULL,';
        $sql[] = '  `attempts` tinyint(3) unsigned NOT NULL,';
        $sql[] = '  `reserved_at` int(10) unsigned DEFAULT NULL,';
        $sql[] = '  `available_at` int(10) unsigned NOT NULL,';
        $sql[] = '  `created_at` int(10) unsigned NOT NULL,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  KEY `jobs_queue_index` (`queue`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = 'DROP TABLE IF EXISTS `job_batches`;';
        $sql[] = 'CREATE TABLE `job_batches` (';
        $sql[] = '  `id` varchar(255) NOT NULL,';
        $sql[] = '  `name` varchar(255) NOT NULL,';
        $sql[] = '  `total_jobs` int(11) NOT NULL,';
        $sql[] = '  `pending_jobs` int(11) NOT NULL,';
        $sql[] = '  `failed_jobs` int(11) NOT NULL,';
        $sql[] = '  `failed_job_ids` longtext NOT NULL,';
        $sql[] = '  `options` mediumtext DEFAULT NULL,';
        $sql[] = '  `cancelled_at` int(11) DEFAULT NULL,';
        $sql[] = '  `created_at` int(11) NOT NULL,';
        $sql[] = '  `finished_at` int(11) DEFAULT NULL,';
        $sql[] = '  PRIMARY KEY (`id`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = 'DROP TABLE IF EXISTS `failed_jobs`;';
        $sql[] = 'CREATE TABLE `failed_jobs` (';
        $sql[] = '  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = '  `uuid` varchar(255) NOT NULL,';
        $sql[] = '  `connection` text NOT NULL,';
        $sql[] = '  `queue` text NOT NULL,';
        $sql[] = '  `payload` longtext NOT NULL,';
        $sql[] = '  `exception` longtext NOT NULL,';
        $sql[] = '  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,';
        $sql[] = '  PRIMARY KEY (`id`),';
        $sql[] = '  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';

        // 11. Migrations table
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Table structure for table `migrations`';
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = 'DROP TABLE IF EXISTS `migrations`;';
        $sql[] = 'CREATE TABLE `migrations` (';
        $sql[] = '  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,';
        $sql[] = '  `migration` varchar(255) NOT NULL,';
        $sql[] = '  `batch` int(11) NOT NULL,';
        $sql[] = '  PRIMARY KEY (`id`)';
        $sql[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $sql[] = '';
        $sql[] = $this->buildInsertStatements('migrations');
        $sql[] = '';

        $sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $sql[] = '-- Export completed successfully.';

        return implode("\n", $sql);
    }

    /**
     * Generate SQLite dump.
     */
    protected function generateSqliteExport(): string
    {
        $now = now()->toDateTimeString();
        $sql = [];

        $sql[] = '-- ========================================================';
        $sql[] = '-- Globaltronics Warehouse Management System (GBTX WHMS)';
        $sql[] = '-- Complete SQLite Database Dump (Schema + Active Data)';
        $sql[] = "-- Generated on: {$now}";
        $sql[] = '-- Target DBMS: SQLite 3';
        $sql[] = '-- ========================================================';
        $sql[] = '';
        $sql[] = 'PRAGMA foreign_keys = OFF;';
        $sql[] = '';

        $tables = [
            'roles',
            'users',
            'role_user',
            'inventory_items',
            'srf_requisitions',
            'transaction_notifications',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'migrations',
        ];

        // Fetch sqlite master schema
        foreach ($tables as $table) {
            $schemaRow = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?", [$table]);
            if ($schemaRow && ! empty($schemaRow->sql)) {
                $sql[] = "DROP TABLE IF EXISTS \"{$table}\";";
                $sql[] = $schemaRow->sql.';';
                $sql[] = '';
                $insertSql = $this->buildInsertStatements($table, true);
                if (! empty($insertSql)) {
                    $sql[] = $insertSql;
                    $sql[] = '';
                }
            }
        }

        // Fetch indexes
        $indexes = DB::select("SELECT sql FROM sqlite_master WHERE type = 'index' AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%'");
        foreach ($indexes as $idx) {
            $sql[] = $idx->sql.';';
        }

        $sql[] = '';
        $sql[] = 'PRAGMA foreign_keys = ON;';

        return implode("\n", $sql);
    }

    /**
     * Build INSERT INTO statements for a given table.
     */
    protected function buildInsertStatements(string $table, bool $isSqlite = false): string
    {
        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            return '';
        }

        $lines = [];
        $lines[] = "-- Dumping data for table `{$table}` (".$rows->count().' records)';

        $firstRow = (array) $rows->first();
        $columns = array_keys($firstRow);
        $colList = implode('`, `', $columns);
        $quoteChar = $isSqlite ? '"' : '`';
        $formattedCols = $quoteChar.implode("{$quoteChar}, {$quoteChar}", $columns).$quoteChar;

        $batchSize = 50;
        $chunks = $rows->chunk($batchSize);

        foreach ($chunks as $chunk) {
            $valueRows = [];
            foreach ($chunk as $row) {
                $values = [];
                foreach ((array) $row as $val) {
                    if (is_null($val)) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($val) && ! is_string($val)) {
                        $values[] = (string) $val;
                    } else {
                        $escaped = str_replace(['\\', "'", "\0", "\n", "\r"], ['\\\\', "\\'", '\\0', '\\n', '\\r'], (string) $val);
                        $values[] = "'{$escaped}'";
                    }
                }
                $valueRows[] = '('.implode(', ', $values).')';
            }

            $lines[] = "INSERT INTO {$quoteChar}{$table}{$quoteChar} ({$formattedCols}) VALUES\n".implode(",\n", $valueRows).';';
        }

        return implode("\n", $lines);
    }
}
