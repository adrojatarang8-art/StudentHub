-- PRACTICAL 13: PHP JSON API + AJAX/Fetch EVENTS CRUD.
-- Import into studenthub_db. Preserves Practical 8-11 records and user accounts.
-- Safe to re-import: test event titles will not be duplicated.
CREATE DATABASE IF NOT EXISTS `studenthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
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

-- P13 demo events. Prior events/registrations are NOT changed or removed.
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`)
 SELECT 'P13 AJAX API Demo Workshop','2026-11-04','Computer Lab 1','Workshop','Hands-on PHP API workshop with AJAX Fetch demo.'
 WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P13 AJAX API Demo Workshop');
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`)
 SELECT 'P13 JavaScript Fetch Seminar','2026-11-11','Seminar Hall A','Seminar','Learn GET and POST operations using JavaScript Fetch.'
 WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P13 JavaScript Fetch Seminar');
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`)
 SELECT 'P13 MySQL API Challenge','2026-11-18','Innovation Lab','Technical','Practice prepared MySQLi statements and JSON validation.'
 WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P13 MySQL API Challenge');
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`)
 SELECT 'P13 Creative Coding Day','2026-12-02','Lab 2','Cultural','Create an interactive front end without page reload.'
 WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P13 Creative Coding Day');
INSERT INTO `events` (`title`,`event_date`,`venue`,`category`,`description`)
 SELECT 'P13 Web Project Showcase','2026-12-09','Main Auditorium','Technical','Student project demonstration of REST-like JSON endpoints.'
 WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `title`='P13 Web Project Showcase');

-- Verify the dataset after import (read-only):
SELECT `event_id`,`title`,`event_date`,`category` FROM `events` ORDER BY `event_id` DESC LIMIT 10;
