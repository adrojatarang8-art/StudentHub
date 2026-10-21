-- ======================================================
-- Database: event_db
-- Practical 12: Event Management CRUD with Poster Upload
-- ======================================================

CREATE DATABASE IF NOT EXISTS `event_db`;
USE `event_db`;

-- 1. Events Table
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(150) NOT NULL,
    `status` ENUM('Open', 'Closed') DEFAULT 'Open',
    `poster` VARCHAR(255) DEFAULT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Audit Logs Table (Advanced Extension)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT,
    `action` VARCHAR(50) NOT NULL,
    `details` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Seed Sample Events
INSERT INTO `events` (`title`, `category`, `event_date`, `venue`, `status`, `poster`, `description`) VALUES
('TechXplore 2026', 'Technical', '2026-11-20', 'Main Campus Auditorium', 'Open', NULL, 'Annual national level technical symposium and hackathon.'),
('Vibrant Rhythm', 'Cultural', '2026-12-10', 'Open Air Amphitheatre', 'Open', NULL, 'Inter-college dance, music, and drama cultural festival.'),
('Cloud Computing Hands-on', 'Workshop', '2026-11-05', 'Computer Lab 3', 'Closed', NULL, 'Practical hands-on workshop covering AWS and Docker deployments.');
