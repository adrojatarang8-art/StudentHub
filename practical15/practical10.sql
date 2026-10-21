-- Practical 10 - safe upgrade for the EXISTING studenthub_db database.
-- First import studenthub_db.sql and secure_accounts.sql if Practical 8/9 tables are absent.
-- Select studenthub_db in phpMyAdmin before importing this script.
-- Can be re-imported: existing accounts, students, registrations and events are preserved.

CREATE TABLE IF NOT EXISTS `user_accounts` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(40) NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `mobile` VARCHAR(10) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student','admin') NOT NULL DEFAULT 'student',
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_account_username` (`username`),
  UNIQUE KEY `uq_account_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Idempotent upgrade for user_accounts that Practical 9 already created:
SET @role_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_accounts' AND COLUMN_NAME = 'role');
SET @role_ddl := IF(@role_exists = 0,
    'ALTER TABLE user_accounts ADD COLUMN `role` ENUM(''student'',''admin'') NOT NULL DEFAULT ''student'' AFTER `password_hash`',
    'SELECT 1');
PREPARE p10_stmt FROM @role_ddl; EXECUTE p10_stmt; DEALLOCATE PREPARE p10_stmt;

SET @last_login_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_accounts' AND COLUMN_NAME = 'last_login_at');
SET @last_login_ddl := IF(@last_login_exists = 0,
    'ALTER TABLE user_accounts ADD COLUMN `last_login_at` DATETIME DEFAULT NULL AFTER `role`',
    'SELECT 1');
PREPARE p10_stmt FROM @last_login_ddl; EXECUTE p10_stmt; DEALLOCATE PREPARE p10_stmt;

CREATE TABLE IF NOT EXISTS `account_audit_log` (
  `audit_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(40) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`)
      REFERENCES `user_accounts` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Only a selector is public. The validator is NEVER stored; only its SHA-256 hash is stored.
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `token_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `selector` CHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `token_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `uq_remember_selector` (`selector`),
  KEY `idx_remember_user` (`user_id`),
  KEY `idx_remember_expiry` (`expires_at`),
  CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`)
      REFERENCES `user_accounts` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- After creating an account in secure_account.php, promote EXACTLY ONE test user to admin:
-- UPDATE user_accounts SET role = 'admin' WHERE username = 'your_admin_username';
-- Verify: SELECT user_id,username,email,role,last_login_at FROM user_accounts;
-- Verify remember tokens: SELECT token_id,user_id,expires_at FROM remember_tokens;
