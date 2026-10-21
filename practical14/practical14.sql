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
