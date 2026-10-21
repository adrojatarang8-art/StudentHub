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

-- Optional sample events; insert only once, without changing any earlier event rows.
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`,`event_status`)
SELECT 'P12 Design and Innovation Workshop','2026-11-06','Computer Lab 2','Workshop','Practical 12 poster upload and event management demonstration.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P12 Design and Innovation Workshop');
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`,`event_status`)
SELECT 'P12 Tech Showcase','2026-11-13','Main Auditorium','Technical','Presentation of computer engineering mini projects.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P12 Tech Showcase');
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`,`event_status`)
SELECT 'P12 Cultural Poster Competition','2026-11-21','Activity Centre','Cultural','Student poster design competition.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P12 Cultural Poster Competition');

-- Read-only check after import:
SELECT `event_id`, `title`, `category`, `event_status`, `poster_path` FROM `events` ORDER BY `event_id` DESC LIMIT 20;
