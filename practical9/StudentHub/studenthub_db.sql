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
