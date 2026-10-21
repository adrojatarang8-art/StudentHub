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
