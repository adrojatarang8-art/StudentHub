-- STUDENTHUB PRACTICAL 15 — EXISTING Practical 14 DATABASE UPGRADE
-- Select studenthub_db in phpMyAdmin and import THIS file once.
-- Adds Practical 12 event poster fields and logs if missing; retains existing data.
-- No DROP, DELETE or destructive data reset; conditional ALTER statements.
-- Also rechecks Practical 14 audit schema for safe integration.
-- For a completely new/empty database use sql/Practical15_Fresh_Install.sql INSTEAD.

-- PRACTICAL 12: EVENT MANAGEMENT + VALIDATED POSTER UPLOAD
-- Existing StudentHub installation: import this file into studenthub_db.
-- Re-import safe: does not erase existing events, students, registrations or accounts.
USE `studenthub_db`;

CREATE TABLE IF NOT EXISTS `events` (
  `event_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `event_date` DATE NOT NULL,
  `venue` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add two new columns only if missing (compatible with MySQL 8 / MariaDB on XAMPP).
SET @p12_has_status := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='events' AND COLUMN_NAME='event_status');
SET @p12_sql := IF(@p12_has_status=0, 'ALTER TABLE `events` ADD COLUMN `event_status` VARCHAR(10) NOT NULL DEFAULT ''open''', 'SELECT 1');
PREPARE p12_cmd FROM @p12_sql; EXECUTE p12_cmd; DEALLOCATE PREPARE p12_cmd;

SET @p12_has_poster := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='events' AND COLUMN_NAME='poster_path');
SET @p12_sql := IF(@p12_has_poster=0, 'ALTER TABLE `events` ADD COLUMN `poster_path` VARCHAR(255) DEFAULT NULL', 'SELECT 1');
PREPARE p12_cmd FROM @p12_sql; EXECUTE p12_cmd; DEALLOCATE PREPARE p12_cmd;

CREATE TABLE IF NOT EXISTS `event_audit_log` (
  `audit_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `action` VARCHAR(20) NOT NULL,
  `details` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  INDEX `idx_p12_event_audit` (`event_id`),
  INDEX `idx_p12_audit_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No additional sample event rows inserted into the existing database.

-- Read-only check after import:
SELECT `event_id`, `title`, `category`, `event_status`, `poster_path` FROM `events` ORDER BY `event_id` DESC LIMIT 20;


-- REAPPLY P14 AUDIT SCHEMA (safe when already imported):
-- PRACTICAL 14: Role-Based Admin Dashboard + Audit Log + Account Status
-- Import into EXISTING studenthub_db using phpMyAdmin -> Import.
-- Preserves existing students, events, registrations, users and login history.
-- Re-import safe: conditional column addition and CREATE TABLE IF NOT EXISTS.
CREATE DATABASE IF NOT EXISTS `studenthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

-- Previous Practical 10 tables (user_accounts / role / account_audit_log)
-- and Practical 8 tables (students / events / registrations) must already exist.
SET @p14_status_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_accounts'
      AND COLUMN_NAME = 'status'
);
SET @p14_status_ddl := IF(@p14_status_exists = 0,
    'ALTER TABLE `user_accounts` ADD COLUMN `status` ENUM(''active'',''inactive'') NOT NULL DEFAULT ''active'' AFTER `role`',
    'SELECT 1');
PREPARE p14_statement FROM @p14_status_ddl;
EXECUTE p14_statement;
DEALLOCATE PREPARE p14_statement;

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `audit_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(40) NOT NULL,
    `entity_type` VARCHAR(40) NOT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `details` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`audit_id`),
    KEY `idx_p14_created` (`created_at`),
    KEY `idx_p14_actor` (`admin_user_id`),
    KEY `idx_p14_entity` (`entity_type`, `entity_id`),
    CONSTRAINT `fk_p14_audit_actor` FOREIGN KEY (`admin_user_id`)
        REFERENCES `user_accounts` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Read-only verification. Adding rows is done by real admin actions in the PHP UI.
SELECT `user_id`, `username`, `role`, `status` FROM `user_accounts` ORDER BY `user_id`;
SELECT `audit_id`, `action`, `entity_type`, `created_at` FROM `audit_logs` ORDER BY `audit_id` DESC LIMIT 10;

-- Verify integrated tables. The actual running database is not included in this ZIP.
SHOW TABLES;
SELECT COUNT(*) AS total_students FROM students;
SELECT COUNT(*) AS total_events FROM events;
SELECT COUNT(*) AS audit_entries FROM audit_logs;
