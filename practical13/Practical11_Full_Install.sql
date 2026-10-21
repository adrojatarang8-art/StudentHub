-- PRACTICAL 11 - COMPLETE FRESH XAMPP INSTALL (PRACTICALS 8-11)
-- Import this ONE file into phpMyAdmin on a FRESH database. Existing tables/records are preserved.
-- Do NOT also import earlier practical SQL if you use this complete installer.



-- ===== studenthub_db.sql =====
-- StudentHub Practical 8: MySQL schema + seed data
-- Import into phpMyAdmin (Import tab).
CREATE DATABASE IF NOT EXISTS studenthub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub_db;

-- 3NF: student and event facts stored once; registrations is their junction table.
CREATE TABLE IF NOT EXISTS students (
 student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 enrollment VARCHAR(20) NOT NULL UNIQUE,
 full_name VARCHAR(100) NOT NULL,
 course VARCHAR(60) NOT NULL,
 semester TINYINT UNSIGNED NOT NULL,
 city VARCHAR(80) NOT NULL,
 skill VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
 event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(150) NOT NULL,
 event_date DATE NOT NULL,
 venue VARCHAR(150) NOT NULL,
 category VARCHAR(50) NOT NULL,
 description TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
 registration_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_id INT UNSIGNED NOT NULL,
 event_id INT UNSIGNED NOT NULL,
 registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_student_event (student_id, event_id),
 CONSTRAINT fk_registration_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_registration_event FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Seed records: based on the original Practical 7 JSON samples.
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (1,'25CE101','Aarav Patel','B.Tech CE',3,'Anand','C++') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (2,'25CE102','Diya Shah','B.Tech CE',3,'Vadodara','Java') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (3,'25CE103','Vivaan Mehta','B.Tech CE',3,'Ahmedabad','JavaScript') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (4,'25CE104','Anaya Desai','B.Tech CE',3,'Surat','HTML/CSS') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (5,'25CE105','Krish Joshi','B.Tech CE',3,'Rajkot','Python') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (6,'25IT106','Myra Trivedi','B.Tech IT',3,'Nadiad','UI Design') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (7,'25IT107','Arjun Rana','B.Tech IT',3,'Bharuch','Networking') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (8,'25IT108','Ira Bhatt','B.Tech IT',3,'Gandhinagar','SQL') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (9,'25CS109','Reyansh Modi','B.Tech CSE',3,'Mehsana','DSA') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (10,'25CS110','Kiara Dave','B.Tech CSE',3,'Anand','React') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (11,'25CE111','Dev Panchal','B.Tech CE',3,'Morbi','Arduino') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (12,'25IT112','Riya Vyas','B.Tech IT',3,'Vadodara','PHP') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (13,'25CS113','Advik Soni','B.Tech CSE',3,'Ahmedabad','Git') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (14,'25CE114','Sara Patel','B.Tech CE',3,'Surat','Web Development') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (student_id,enrollment,full_name,course,semester,city,skill) VALUES (15,'25IT115','Kabir Shah','B.Tech IT',3,'Rajkot','Cyber Security') ON DUPLICATE KEY UPDATE student_id=student_id;

INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (1,'Tech Fest 2026','2026-08-20','College Auditorium','Technical','Technology exhibitions, project demonstrations and competitions.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (2,'Sports Day','2026-09-10','College Ground','Sports','Inter-department sports competitions and team events.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (3,'Coding Workshop','2026-09-25','Computer Lab','Workshop','Hands-on coding session for web and programming fundamentals.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (4,'AI Awareness Seminar','2026-10-03','Seminar Hall A','Seminar','Introduction to responsible AI and modern applications.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (5,'Web Design Challenge','2026-10-08','Lab 2','Technical','Frontend design challenge using HTML, CSS and JavaScript.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (6,'Career Guidance Talk','2026-10-15','Main Hall','Seminar','Industry guidance on internships, skills and placements.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (7,'Cyber Security Workshop','2026-10-22','Network Lab','Workshop','Basics of cyber safety, secure browsing and network security.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (8,'Cultural Evening','2026-11-02','Open Air Theatre','Cultural','Music, dance and student cultural performances.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (9,'Hackathon 2026','2026-11-09','Innovation Center','Technical','Team-based problem solving and prototype development.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (10,'Photography Contest','2026-11-14','Student Activity Center','Cultural','Campus photography competition for students.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (11,'Data Structures Bootcamp','2026-11-20','Lab 1','Workshop','Revision of stacks, queues, linked lists and trees.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (12,'Startup Talk','2026-11-27','Seminar Hall B','Seminar','Entrepreneurship session with startup examples and ideas.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (13,'Cricket Tournament','2026-12-03','College Ground','Sports','Department-level cricket tournament.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (14,'Poster Presentation','2026-12-08','Academic Block','Technical','Students present posters on emerging technology topics.') ON DUPLICATE KEY UPDATE event_id=event_id;
INSERT INTO events (event_id,title,event_date,venue,category,description) VALUES (15,'Annual Day','2026-12-18','College Auditorium','Cultural','Annual celebration with awards and performances.') ON DUPLICATE KEY UPDATE event_id=event_id;

-- Sample registration rows (repeat imports without duplicates)
INSERT IGNORE INTO registrations (student_id,event_id) VALUES (1,1);
INSERT IGNORE INTO registrations (student_id,event_id) VALUES (1,3);
INSERT IGNORE INTO registrations (student_id,event_id) VALUES (2,2);
INSERT IGNORE INTO registrations (student_id,event_id) VALUES (3,3);
INSERT IGNORE INTO registrations (student_id,event_id) VALUES (4,4);
INSERT IGNORE INTO registrations (student_id,event_id) VALUES (5,5);

-- Useful verification queries:
-- SHOW TABLES;
-- SELECT * FROM students;
-- SELECT * FROM events;
-- SELECT r.registration_id,s.full_name,e.title,r.registered_at
-- FROM registrations r JOIN students s ON s.student_id=r.student_id
-- JOIN events e ON e.event_id=r.event_id;



-- ===== secure_accounts.sql =====
-- Practical 9 migration: import INTO existing studenthub_db in phpMyAdmin.
-- Keeps Practical 8 students / events / registrations intact.
CREATE TABLE IF NOT EXISTS `user_accounts` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(40) NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `mobile` VARCHAR(10) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_account_username` (`username`),
  UNIQUE KEY `uq_account_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `account_audit_log` (
  `audit_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(40) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- ===== practical10.sql =====
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



-- ===== practical11.sql =====
-- STUDENTHUB PRACTICAL 11 - STUDENT MANAGEMENT UPGRADE
-- Safe to re-import. Does not drop students, accounts, events, or registrations.
-- Import into phpMyAdmin: studenthub_db or simply Import this file as it selects the DB.
-- Run Practical11_Full_Install.sql INSTEAD on a completely fresh XAMPP database.

CREATE DATABASE IF NOT EXISTS `studenthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

-- On a standalone Practical 11 install, create the student table if absent.
-- On the existing Practical 8/10 project, preserve existing rows and add new columns.
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment` VARCHAR(20) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(254) DEFAULT NULL,
  `course` VARCHAR(60) NOT NULL,
  `semester` TINYINT UNSIGNED NOT NULL,
  `city` VARCHAR(80) NOT NULL,
  `skill` VARCHAR(80) NOT NULL,
  `status` ENUM('active','deleted') NOT NULL DEFAULT 'active',
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `uq_students_enrollment` (`enrollment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- phpMyAdmin / MySQL 8 / MariaDB compatible, idempotent column migration.
SET @p11_email_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='students' AND COLUMN_NAME='email');
SET @p11_sql := IF(@p11_email_exists=0,
  'ALTER TABLE students ADD COLUMN email VARCHAR(254) NULL AFTER full_name',
  'SELECT 1');
PREPARE p11_stmt FROM @p11_sql; EXECUTE p11_stmt; DEALLOCATE PREPARE p11_stmt;

SET @p11_status_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='students' AND COLUMN_NAME='status');
SET @p11_sql := IF(@p11_status_exists=0,
  'ALTER TABLE students ADD COLUMN status ENUM(''active'',''deleted'') NOT NULL DEFAULT ''active'' AFTER skill',
  'SELECT 1');
PREPARE p11_stmt FROM @p11_sql; EXECUTE p11_stmt; DEALLOCATE PREPARE p11_stmt;

SET @p11_deleted_at_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='students' AND COLUMN_NAME='deleted_at');
SET @p11_sql := IF(@p11_deleted_at_exists=0,
  'ALTER TABLE students ADD COLUMN deleted_at DATETIME NULL AFTER status',
  'SELECT 1');
PREPARE p11_stmt FROM @p11_sql; EXECUTE p11_stmt; DEALLOCATE PREPARE p11_stmt;

-- Unique emails allow many NULL values for older Practical 8 students.
SET @p11_email_index_exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='students' AND INDEX_NAME='uq_students_email');
SET @p11_sql := IF(@p11_email_index_exists=0,
  'ALTER TABLE students ADD UNIQUE KEY uq_students_email (email)',
  'SELECT 1');
PREPARE p11_stmt FROM @p11_sql; EXECUTE p11_stmt; DEALLOCATE PREPARE p11_stmt;

SET @p11_filter_index_exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='students' AND INDEX_NAME='idx_students_course_status');
SET @p11_sql := IF(@p11_filter_index_exists=0,
  'ALTER TABLE students ADD KEY idx_students_course_status (course,status)',
  'SELECT 1');
PREPARE p11_stmt FROM @p11_sql; EXECUTE p11_stmt; DEALLOCATE PREPARE p11_stmt;

-- 20 ADDITIONAL test records; re-import does not duplicate these rows.
-- Emails use reserved example.com addresses (not real students).
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE201','Nirav Shah','p11demo201@example.com','B.Tech CE',3,'Ahmedabad','PHP','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE202','Isha Patel','p11demo202@example.com','B.Tech CSE',3,'Anand','MySQL','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE203','Kunal Mehta','p11demo203@example.com','B.Tech IT',3,'Surat','Java','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE204','Jiya Desai','p11demo204@example.com','B.Tech CE',3,'Vadodara','Python','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE205','Manav Trivedi','p11demo205@example.com','B.Tech CSE',3,'Nadiad','DSA','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE206','Riddhi Joshi','p11demo206@example.com','B.Tech IT',3,'Rajkot','HTML/CSS','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE207','Rohan Patel','p11demo207@example.com','B.Tech CE',3,'Bharuch','JavaScript','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE208','Dhruvi Shah','p11demo208@example.com','B.Tech CSE',3,'Gandhinagar','React','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE209','Yash Soni','p11demo209@example.com','B.Tech IT',3,'Ahmedabad','C++','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE210','Meera Dave','p11demo210@example.com','B.Tech CE',3,'Anand','Networking','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE211','Het Panchal','p11demo211@example.com','B.Tech CSE',3,'Surat','PHP','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE212','Aditi Vyas','p11demo212@example.com','B.Tech IT',3,'Vadodara','MySQL','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE213','Harsh Modi','p11demo213@example.com','B.Tech CE',3,'Nadiad','Java','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE214','Nisha Rana','p11demo214@example.com','B.Tech CSE',3,'Rajkot','Python','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE215','Devanshi Bhatt','p11demo215@example.com','B.Tech IT',3,'Bharuch','DSA','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE216','Om Choksi','p11demo216@example.com','B.Tech CE',3,'Gandhinagar','HTML/CSS','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE217','Pooja Brahmbhatt','p11demo217@example.com','B.Tech CSE',3,'Ahmedabad','JavaScript','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE218','Darsh Gupta','p11demo218@example.com','B.Tech IT',3,'Anand','React','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE219','Mitali Shah','p11demo219@example.com','B.Tech CE',3,'Surat','C++','active') ON DUPLICATE KEY UPDATE student_id=student_id;
INSERT INTO students (enrollment,full_name,email,course,semester,city,skill,status) VALUES ('25DCE220','Parth Joshi','p11demo220@example.com','B.Tech CSE',3,'Vadodara','Networking','active') ON DUPLICATE KEY UPDATE student_id=student_id;

-- Verification queries for the practical submission:
-- SELECT COUNT(*) AS total_students FROM students;
-- SELECT student_id,enrollment,full_name,email,course,semester,status FROM students ORDER BY student_id DESC;
-- SELECT status,COUNT(*) AS records FROM students GROUP BY status;
-- SELECT * FROM students WHERE full_name LIKE '%Patel%' OR email LIKE '%example.com%';
-- SELECT * FROM students WHERE course='B.Tech CSE' AND status='active';
-- SQL proof of soft delete is demonstrated by PHP buttons; DO NOT run destructive DELETE statements.

