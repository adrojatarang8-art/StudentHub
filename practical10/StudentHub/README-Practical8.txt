STUDENTHUB - PRACTICAL 8 - MYSQL / PHP PDO
==========================================

1. Copy the complete Practical7 folder into C:\xampp\htdocs\StudentHub
2. Open XAMPP Control Panel; START Apache AND MySQL.
3. In browser open http://localhost/phpmyadmin/
4. Choose Import -> choose StudentHub/studenthub_db.sql -> click Import / Go.
   The SQL creates studenthub_db, 3 tables and example data.
5. Open http://localhost/StudentHub/db_test.php
   You should see the green 'Database connected successfully' message
   and the students/events/registrations counts.
6. Open http://localhost/StudentHub/practical8.php
   Select one student and one event; click Save Registration in MySQL.
   A green message appears and the new row appears in the table.
7. In phpMyAdmin open studenthub_db -> registrations -> Browse
   to verify that the database contains the new registration.

FILES FOR SUBMISSION
- studenthub_db.sql (schema and sample seed data / importable dump)
- db.php (PDO database connection)
- screenshot showing db_test.php success
- Extra: ER_Model.md, practical8.php, phpMyAdmin tables/registration screenshot

DATABASE
- students: student_id PK, enrollment UNIQUE
- events: event_id PK
- registrations: registration_id PK, student_id FK, event_id FK,
  UNIQUE(student_id, event_id)
- 1 student -> many registrations; 1 event -> many registrations

SECURITY
- PDO exceptions, UTF-8, native parameterized queries, escaped HTML,
  POST + CSRF check for registration and duplicate-entry handling.
- db.php defaults to XAMPP local MySQL username root with empty password.
  If yours differs, configure DB_USER / DB_PASS / DB_HOST / DB_NAME
  environment variables (never publish passwords to GitHub).

IMPORTANT
- Practical 7 register.php and process_registration.php still use
  original CSV/JSON storage exactly as before.
- Practical 8 has separate MySQL *event* registrations at practical8.php.
- MySQL cannot be automatically activated by a ZIP: import SQL in
  phpMyAdmin first, then capture an actual connection screenshot.
- A screenshot is not included because a running local XAMPP/MySQL
  environment is required for a genuine successful screenshot.

SCREENSHOTS
Fig 1: phpMyAdmin showing studenthub_db and 3 tables
Fig 2: Students/events/registrations table structure and FK relations
Fig 3: db_test.php showing successful PDO database connection
Fig 4: practical8.php displaying registration form & sample records
Fig 5: successful new registration and phpMyAdmin Browse result
