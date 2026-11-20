-- ========================================================
-- Globaltronics Warehouse Management System (GBTX WHMS)
-- Complete SQLite Database Dump (Schema + Active Data)
-- Generated on: 2026-11-20 00:38:10
-- Target DBMS: SQLite 3
-- ========================================================

PRAGMA foreign_keys = OFF;

DROP TABLE IF EXISTS "roles";
CREATE TABLE "roles" ("id" integer primary key autoincrement not null, "name" varchar not null, "slug" varchar not null, "description" text, "badge_color" varchar not null default 'blue', "is_system" tinyint(1) not null default '0', "created_at" datetime, "updated_at" datetime);

-- Dumping data for table `roles` (4 records)
INSERT INTO "roles" ("id", "name", "slug", "description", "badge_color", "is_system", "created_at", "updated_at") VALUES
(1, 'IT Administrator', 'it-admin', 'Full administrative access to user accounts, roles, security policies, and system configs.', 'purple', 1, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(2, 'Warehouse Admin', 'warehouse-admin', 'Oversees warehouse floor operations, storage bays, dispatch queues, and inventory flows.', 'amber', 1, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(3, 'Warehouse Staff', 'warehouse-staff', 'Floor operational personnel managing item picking, pallet loading, and barcode registration.', 'emerald', 0, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(7, 'Technical', 'technical', 'Technical personnel handling Stock Requisition Forms (SRF), site installation hardware, and diagnostics.', 'cyan', 0, '2026-10-06 03:49:06', '2026-10-06 03:49:06');

DROP TABLE IF EXISTS "users";
CREATE TABLE "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, "is_admin" tinyint(1) not null default '0');

-- Dumping data for table `users` (5 records)
INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "remember_token", "created_at", "updated_at", "is_admin") VALUES
(1, 'Mark Joseph Estoesta', 'mark.estoesta@globaltronics.net', NULL, '$2y$12$daM8abN8XDOPx.mfO97Shu0Zt47/O1s9sgFym7ZXRLuCN/CMtwtwC', '8d5nvmhPq8ZtmbIX6rB4wwPdcuL7TKGCtSib5ksvys1kxoXHCpED7HuXfpTR', '2026-09-25 06:31:10', '2026-10-06 04:35:41', 0),
(2, 'Joshua Labios', 'joshua.labios@globaltronics.net', NULL, '$2y$12$PBhNwHfuSqZ.73MQrpZEneFZevL/qFZklypAFVSgpDi7jUDAIXjhq', '64m8ikmGnpucC3yZ47iM1Ym5sGszw953jFOYbUojBigefWfIB3dr4o8zhfhB', '2026-09-25 06:31:10', '2026-10-06 03:49:53', 0),
(3, 'Warehouse Staff User', 'staff@globaltronics.net', NULL, '$2y$12$Z2XsrlQCHWC4hc6EZu.q.ezd.2wKI9DwG8CMpkkoO9Y9Gw0Kq4Px6', 'R8lQoJMwqcxboUAZobBTeOXqupVUQTo2zpOL6ZGy50EftcDtPhqQqeAaz7gx', '2026-09-25 06:31:10', '2026-10-06 03:49:53', 0),
(4, 'Ariel Moro', 'ariel.moro@globaltronics.net', NULL, '$2y$12$2DwBWwV8GBSE//BmxbyYR.FDNR9cfh1ejpJSaxCMYtFB8rdXmRNhm', 'HIVKq0D3E9m37BYx1JIdaoXiExkpnhNTOMjLOHkFxJzz5BL60C9wF2hvk3ZH', '2026-10-06 03:27:48', '2026-10-06 03:49:53', 0),
(6, 'Charles Quiachon', 'charles.quiachon@globaltronics.net', NULL, '$2y$12$D/CMTp5fVbH3pVoTeHbcZOrbJ11pqwf/lYcf1SGGJ6LRAGN0XSHES', NULL, '2026-10-06 04:36:48', '2026-10-06 04:36:48', 0);

DROP TABLE IF EXISTS "role_user";
CREATE TABLE "role_user" ("user_id" integer not null, "role_id" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, foreign key("role_id") references "roles"("id") on delete cascade, primary key ("user_id", "role_id"));

-- Dumping data for table `role_user` (5 records)
INSERT INTO "role_user" ("user_id", "role_id", "created_at", "updated_at") VALUES
(1, 1, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(2, 2, '2026-10-05 05:38:08', '2026-10-05 05:38:08'),
(3, 3, '2026-10-05 05:38:09', '2026-10-05 05:38:09'),
(4, 7, '2026-10-06 03:49:07', '2026-10-06 03:49:07'),
(6, 2, '2026-10-06 04:36:48', '2026-10-06 04:36:48');

DROP TABLE IF EXISTS "srf_requisitions";
CREATE TABLE "srf_requisitions" ("id" integer primary key autoincrement not null, "srf_number" varchar not null, "sso_number" varchar not null, "project_name" varchar not null, "quantity" integer not null, "stock_status" varchar not null default ('available_reserved'), "status" varchar not null default ('pending'), "prepared_by" varchar not null, "user_id" integer, "verified_by_user_id" integer, "verified_by_name" varchar, "verified_at" datetime, "verification_notes" text, "notes" text, "created_at" datetime, "updated_at" datetime, "client" varchar, "po_number" varchar, "date_needed" date, "requisition_date" date, "uom" varchar not null default ('PCS'), "remarks" text, "department" varchar default ('TECHNICAL'), "noted_by" varchar, "pre_approved_by" varchar, "approved_by" varchar default ('Macy Guido Lee'), "item_model" varchar, "item_manufacturer" varchar, "item_category" varchar, "item_description" text, foreign key("verified_by_user_id") references users("id") on delete set null on update no action, foreign key("user_id") references users("id") on delete set null on update no action);

-- Dumping data for table `srf_requisitions` (2 records)
INSERT INTO "srf_requisitions" ("id", "srf_number", "sso_number", "project_name", "quantity", "stock_status", "status", "prepared_by", "user_id", "verified_by_user_id", "verified_by_name", "verified_at", "verification_notes", "notes", "created_at", "updated_at", "client", "po_number", "date_needed", "requisition_date", "uom", "remarks", "department", "noted_by", "pre_approved_by", "approved_by", "item_model", "item_manufacturer", "item_category", "item_description") VALUES
(3, '2026 - 0957', 'SSO-2026-0101', 'SM PRIME HOLDINGS', 1, 'available_reserved', 'on_hold', 'JOSHUA', 2, 2, 'Joshua Labios', '2026-10-07 04:14:57', 'Placed on hold pending further review.', NULL, '2026-10-07 04:04:10', '2026-10-07 04:14:57', 'SM PRIME HOLDINGS', 'PO- 2026-0123', '2026-10-10 00:00:00', '2026-10-05 00:00:00', 'PCS', 'PACKAGING', 'WAREHOUSE', 'FELIX', 'TEDDY BAJETA', 'Macy Guido Lee', 'UDT 2.5', 'UNILUMIN', 'CENTRALIZED LED INVENTORY', 'LED DISPLAY (960 X 960MM) - OUTDOOR'),
(4, '2026 - 0957', 'SSO-2026-0101', 'SM PRIME HOLDINGS', 2, 'available_reserved', 'on_hold', 'JOSHUA', 2, 2, 'Joshua Labios', '2026-10-07 04:14:57', 'Placed on hold pending further review.', NULL, '2026-10-07 04:04:10', '2026-10-07 04:14:57', 'SM PRIME HOLDINGS', 'PO- 2026-0123', '2026-10-10 00:00:00', '2026-10-05 00:00:00', 'BOXES', 'TEST', 'WAREHOUSE', 'FELIX', 'TEDDY BAJETA', 'Macy Guido Lee', 'UDAII 1.5 COB', 'UNILUMIN', 'CENTRALIZED LED INVENTORY', 'LED DISPLAY (640 X 480MM) - INDOOR');

DROP TABLE IF EXISTS "transaction_notifications";
CREATE TABLE "transaction_notifications" ("id" integer primary key autoincrement not null, "user_id" integer, "actor_id" integer, "actor_name" varchar not null default 'System', "actor_role" varchar, "title" varchar not null, "message" text not null, "type" varchar not null default 'general', "reference_id" varchar, "is_read" tinyint(1) not null default '0', "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete set null, foreign key("actor_id") references "users"("id") on delete set null);

-- Dumping data for table `transaction_notifications` (4 records)
INSERT INTO "transaction_notifications" ("id", "user_id", "actor_id", "actor_name", "actor_role", "title", "message", "type", "reference_id", "is_read", "created_at", "updated_at") VALUES
(1, NULL, 4, 'Ariel Moro', 'Technical', 'Stock Requisition Form (SRF) Submitted', 'Ariel Moro submitted SRF #2026 - 0957 with 2 item(s) (20 PCS of A0625 (R), 1 PCS of A0625) for ''SM PRIME HOLDINGS MEGAMALL''. Status: PENDING Warehouse Review.', 'srf_submitted', '2026 - 0957', 1, '2026-10-06 06:25:34', '2026-10-06 07:06:53'),
(2, NULL, 6, 'Charles Quiachon', 'Warehouse Admin', 'SRF Verified & Completed', 'Warehouse team member Charles Quiachon verified and approved SRF #2026 - 0957 for project ''SM PRIME HOLDINGS MEGAMALL''. Status updated to COMPLETED.', 'srf_verified', '2026 - 0957', 1, '2026-10-06 06:41:54', '2026-10-06 07:06:53'),
(3, NULL, 2, 'Joshua Labios', 'Warehouse Admin', 'Stock Requisition Form (SRF) Submitted', 'JOSHUA submitted SRF #2026 - 0957 with 2 item(s) (1 PCS of UDT 2.5, 2 BOXES of UDAII 1.5 COB) for ''SM PRIME HOLDINGS''. Status: PENDING Warehouse Review.', 'srf_submitted', '2026 - 0957', 1, '2026-10-07 04:04:10', '2026-10-07 04:08:57'),
(4, NULL, 2, 'Joshua Labios', 'Warehouse Admin', 'SRF Placed On Hold', 'Warehouse team member Joshua Labios placed SRF #2026 - 0957 for project ''SM PRIME HOLDINGS'' ON HOLD.', 'srf_on_hold', '2026 - 0957', 0, '2026-10-07 04:14:57', '2026-10-07 04:14:57');

DROP TABLE IF EXISTS "password_reset_tokens";
CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));

-- Dumping data for table `password_reset_tokens` (1 records)
INSERT INTO "password_reset_tokens" ("email", "token", "created_at") VALUES
('mark.estoesta@globaltronics.net', '$2y$12$gkMt8XXu1Io4L/XA42L9UO6bzKvj5KEhXfOrRoU033urlZgH7iqAO', '2026-09-25 08:01:12');

DROP TABLE IF EXISTS "sessions";
CREATE TABLE "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));

-- Dumping data for table `sessions` (2 records)
INSERT INTO "sessions" ("id", "user_id", "ip_address", "user_agent", "payload", "last_activity") VALUES
('1WeniIisH21KvOE2FzB8zKPIgDLq5FMKnNmviDM1', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJsU252YkwweXdPVWRTWHJEUnR5dmJWUFExNnlYM3BtQmM0OTRjTjQyIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvaW52ZW50b3J5P2NhdGVnb3J5PVBoaWxpcHMlMjBTZXJ2aWNlJTIwVW5pdHMiLCJyb3V0ZSI6ImFkbWluLmludmVudG9yeS5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoyLCJwYXNzd29yZF9oYXNoX3dlYiI6ImJlMGVmNDRkMjIyNmExN2U0NDZhYzdjN2M1NGRjNmFjZjg5MjFjYjA0NWNmMmJiNWM3MzU2ZDFmZjY5MTI5OGEifQ==', 1791357089),
('OU08Ws7ZciYV3gE2nHMp2KyZbcP0dSXMpJ63nfMn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJmZ2d6MUdrSUJIbnA5WEpEd0JuSjlTdHN1aTRjb0hFNWpDR2hCR2dFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1791360700);

DROP TABLE IF EXISTS "cache";
CREATE TABLE "cache" ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));

DROP TABLE IF EXISTS "cache_locks";
CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));

DROP TABLE IF EXISTS "jobs";
CREATE TABLE "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);

DROP TABLE IF EXISTS "job_batches";
CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));

DROP TABLE IF EXISTS "failed_jobs";
CREATE TABLE "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" varchar not null, "queue" varchar not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);

DROP TABLE IF EXISTS "migrations";
CREATE TABLE "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

-- Dumping data for table `migrations` (19 records)
INSERT INTO "migrations" ("id", "migration", "batch") VALUES
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

CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");
CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");
CREATE INDEX "cache_expiration_index" on "cache" ("expiration");
CREATE INDEX "cache_locks_expiration_index" on "cache_locks" ("expiration");
CREATE INDEX "jobs_queue_index" on "jobs" ("queue");
CREATE INDEX "failed_jobs_connection_queue_failed_at_index" on "failed_jobs" ("connection", "queue", "failed_at");
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");
CREATE UNIQUE INDEX "roles_slug_unique" on "roles" ("slug");
CREATE INDEX "srf_requisitions_srf_number_index" on "srf_requisitions" ("srf_number");

PRAGMA foreign_keys = ON;