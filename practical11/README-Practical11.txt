STUDENTHUB PRACTICAL 11 - Student Management CRUD, Search and Filter
===================================================================
StudentHub project updated FROM the attached Practical 10 ZIP.
Your old Practical 7-10 project files are preserved.

A. WHAT IS INCLUDED
------------------
1. student_directory.php  Admin-protected student manager page.
   - CREATE: add a student (INSERT)
   - READ: table of records (SELECT)
   - UPDATE: click Edit and save (UPDATE)
   - DELETE: click Delete, confirm in browser (UPDATE status='deleted')
   - RESTORE: change status to Deleted, click Restore
   - Search by full name, email, course or enrollment; course filter; status filter
   - Pagination (10 per page), total/active/deleted/filtered counters
   - Success or error feedback after operations
2. student_export.php: download the current filtered result set as CSV.
3. p11_student_helpers.php: shared prepared MySQLi query/filter functions.
4. css/style.css: responsive dark theme, matches Practical 10.
5. admin.php: link to Student Management from Admin Dashboard.
6. index.html: new navigation link.
7. practical11.sql: safe upgrade of existing DB, with 20 extra seeded records.
8. Practical11_Full_Install.sql: all SQL from Practical 8-11 in ONE file for a
   brand-new empty XAMPP setup. (Includes earlier P8 15 students and P11 20.)
9. Practical11-Test-Cases.txt: exact testing and screenshot checklist.

B. INSTALL / FIX HTTP 404 (FOR EXISTING PRACTICAL 10)
---------------------------------------------------
1. Stop editing an older extracted project while copying; back it up first.
2. Extract the ZIP contents into C:\xampp\htdocs\ (top folder must be practical11).
3. Confirm this exact file exists on disk:
      C:\xampp\htdocs\practical11\student_directory.php
4. XAMPP: Start Apache and MySQL. Use http://localhost (NOT Live Server).
5. phpMyAdmin -> choose studenthub_db -> Import -> practical11.sql -> Go.
   This script preserves existing student rows, registrations, events,
   user_accounts and passwords; adds email/status/deleted_at columns.
6. Login as ADMIN: http://localhost/practical11/login.php
7. Open: http://localhost/practical11/student_directory.php
8. Alternatively, use Admin Dashboard > Manage Students.

Do not nest practical11 inside another folder. The exact path must be
  C:\xampp\htdocs\practical11\student_directory.php .

C. BRAND-NEW INSTALL (NO DATABASE)
---------------------------------
1. Start Apache/MySQL and go to http://localhost/phpmyadmin.
2. Import Practical11_Full_Install.sql ONCE. It creates studenthub_db,
   Practical 8 students/events/registrations, Practical 9 accounts, Practical
   10 session tables, and Practical 11 columns plus 20 demo students.
3. Visit http://localhost/practical11/secure_account.php to create a test user with YOUR
   chosen username, email and password. Password hashes are generated safely.
4. To grant ADMIN access, run in phpMyAdmin (replace your_username):
      UPDATE user_accounts SET role='admin' WHERE username='your_username';
5. Log in as this user, open admin.php -> Manage Students.
NOTE: All newly registered accounts start as students for security reasons.
Do not guess admin_demo passwords; no insecure default admin is included.

D. DATABASE DESIGN / BACKWARD COMPATIBILITY
------------------------------------------
The existing `students` table already has student_id, enrollment, full_name,
course, semester, city and skill. Existing 15 P8 records don't have email.
The upgrade adds:
  email VARCHAR(254) NULL UNIQUE   (older P8 students remain valid)
  status ENUM('active','deleted') DEFAULT 'active'
  deleted_at DATETIME NULL
and an index on course+status. 20 ADDITIONAL seeded P11 demo rows include
email. Re-importing practical11.sql won't create duplicate P11 test rows.

The default list shows ONLY active records. Soft-deleted rows are STILL present
in MySQL, including their registration relationships. Change Record Status to
'Deleted' or 'All' to view/restore them. NEVER use SQL DELETE to prove soft delete.

E. HOW TO USE
-------------
ADD: Fill Enrollment, Full Name, Email, Course, Semester, City and Skill,
     click Add Student. A green success message appears.
VIEW: New entries appear in table. Pagination automatically changes when >10.
EDIT: Click Edit, change data in the left form and Save Changes.
SEARCH: Enter a name, email, course or enrollment; click Apply.
FILTER: Select Course and Status from dropdowns; click Apply.
DELETE: Click Delete and confirm the browser prompt. No permanent SQL DELETE.
RESTORE: Select Deleted in status filter, click Restore.
EXPORT: After applying filters, click Export CSV. Exports all matched rows,
        not just the current page; includes status and deleted_at fields.
SECURITY: Admin login required; all write operations use prepared MySQLi,
          server validation and CSRF protection; dynamic text is HTML-escaped.

F. DB VERIFICATION (RUN IN phpMyAdmin)
-------------------------------------
USE studenthub_db;
SELECT COUNT(*) AS total_students FROM students;
SELECT student_id,enrollment,full_name,email,course,status,deleted_at
  FROM students ORDER BY student_id DESC LIMIT 25;
SELECT status, COUNT(*) AS count FROM students GROUP BY status;
SELECT * FROM students WHERE full_name LIKE '%Patel%';
SELECT * FROM students WHERE course='B.Tech CSE' AND status='active';

G. SCREENSHOTS FOR LAB SUBMISSION
--------------------------------
1. Student Directory UI listing at least 20 total records / multiple pages.
2. Add Student filled form and successful inserted record.
3. All Students table (SELECT).
4. Edit Student and success confirmation after update.
5. Delete confirmation browser dialog.
6. Deleted status and soft-delete database column proof.
7. Search by name/email/course, Course and Status filter results.
8. Restore student and updated active status.
9. Export CSV viewed in Excel (optional extension).
10. phpMyAdmin students schema and seed rows.
Use only screenshots taken from YOUR running localhost project. This ZIP does
not contain fabricated browser or phpMyAdmin screenshots.

H. TROUBLESHOOTING
-----------------
404: The project folder must be htdocs/practical11.
     Read Part B; check exact path and start Apache.
Redirect to login: normal when not signed in; admin credentials required.
403 Access Denied: signed in as student. Promote YOUR account as explained.
Database missing: import the required SQL for your existing or fresh setup.
Unknown column email/status: import practical11.sql AFTER Practical 10 DB.
Port different from 80? Change localhost URL to include your Apache port.
No MySQL connection: check db credentials/environment in mysqli_db.php.
PHP version: 8.1+ recommended; MySQLi extension with mysqlnd, typical XAMPP.

PLEASE NOTE: Code was reviewed and PHP-linted. Runtime results/screenshots
must be verified on your own XAMPP+MySQL installation before submission.
