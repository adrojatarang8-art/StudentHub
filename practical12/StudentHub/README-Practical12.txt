PRACTICAL 12: EVENT MANAGEMENT CRUD WITH POSTER UPLOAD
========================================================

THIS ZIP HAS ONE TOP-LEVEL FOLDER: practical12
After extracting directly to C:\xampp\htdocs\ the file must exist at:
  C:\xampp\htdocs\practical12\event_management.php
There must NOT be an extra StudentHub or practical11 folder in the URL.

SETUP (USE THE EXISTING STUDENTHUB DATABASE FROM PRACTICAL 11)
-----------------------------------------------------------
1. First back up your existing studenthub_db from phpMyAdmin -> Export.
2. Place/extract practical12 folder directly inside C:\xampp\htdocs\.
3. Start Apache and MySQL in XAMPP.
4. Open http://localhost/phpmyadmin/
5. Click the EXISTING database: studenthub_db.
6. Click Import -> Choose File -> select:
     C:\xampp\htdocs\practical12\practical12.sql
   Click Go. The migration adds event_status, poster_path and event_audit_log.
   It preserves existing users, students, registrations and events.
7. Login at http://localhost/practical12/login.php
   Use your SAME admin username/password as Practical 10/11
   (e.g. p10_admin01 if you still use that account).
8. Open http://localhost/practical12/event_management.php

FOR A COMPLETELY NEW INSTALL WITHOUT ANY DATABASE:
  Import Practical12_Full_Install.sql instead of practical12.sql.
  You still need to register an account in secure_account.php and
  assign role admin for local lab testing if you don't already have one.
  DO NOT import both installers. Never drop the old StudentHub database.

FEATURES
--------
- Add, list, update and delete events using parameterized MySQLi statements
- Event title, valid date, venue, category, description and open/closed status
- Search by title/venue/description and category/status filters + 10/page
- Optional poster upload; server-side MIME + image content checks
- JPG, PNG and WEBP only. Maximum file size = 2 MiB (2,097,152 bytes)
- Unique randomized filenames, no PHP scripts in upload folder
- Poster preview, replacement and removal when editing
- CSRF token checking, admin-only page and server-side input validation
- Delete confirmation. Events with registrations are BLOCKED from delete
  to prevent losing registrations due to the existing foreign-key cascade.
- Success/error messages; a separate event_audit_log for changes

SCREENSHOTS TO COLLECT FOR THE PRACTICAL 12 WORD REPORT
------------------------------------------------------
Figure 1: Practical 12 SQL import completed (phpMyAdmin)
Figure 2: Events table with event_status and poster_path columns (phpMyAdmin)
Figure 3: Event Management Dashboard with events and statistics
Figure 4: New Event Added Successfully
Figure 5: Poster uploaded (valid JPG/PNG/WEBP, shows thumbnail)
Figure 6: Event Updated Successfully (title/venue/status)
Figure 7: Event Category / Open-Closed Filter
Figure 8: Delete Confirmation Dialog (before clicking OK)
Figure 9: Event Deleted Successfully (use an event with 0 registrations)
Figure 10: Invalid Poster Type Rejected (e.g., .txt file)
Figure 11: Validation/Error Message (required field or wrong date)
Figure 12: Audit Log Records (optional extension)

Only include screenshots of actions actually tested. The size >2MB test
is not required if you choose to skip it. Do not claim untested cases passed.

EXAMPLE TEST RECORD (use these values in UI)
--------------------------------------------
Title: Practical 12 Test Workshop
Date: 2026-11-26
Venue: Computer Lab 3
Category: Workshop
Description: PHP CRUD and validated event poster upload demonstration.
Status: Open
Poster: Any genuine local JPG or PNG under 2MB
Edit later: Venue -> Seminar Hall B, Status -> Closed
For the delete test, choose your own new event with NO registrations.

COMMON ISSUES
-------------
404: Confirm htdocs\practical12\event_management.php is present.
Login redirect: Expected when not signed in; log in to practical12/login.php.
403 Forbidden: Log in as an ADMIN (not a student).
Unknown column event_status/poster_path: Import practical12.sql.
Cannot upload: confirm uploads/event_posters exists and PHP can write there;
               check PHP php.ini upload_max_filesize and post_max_size.
Event cannot be deleted: It has student registrations and is intentionally
                         protected. Test deletion with a new unregistered event.
File is too large: Choose a poster under 2MB.
Invalid image: A file extension alone is not accepted. Use a real JPG/PNG/WEBP.
Blank uploads: Ensure file_uploads is enabled in XAMPP PHP configuration.

DATABASE
--------
Name: studenthub_db (same as previous practicals)
Event columns added: event_status, poster_path
New table: event_audit_log
Seed examples: 3 new P12 events; seed is re-import safe by title.
Older Practical 11 StudentHub pages remain in the zip.

MAIN FILES
----------
event_management.php        New secured UI + MySQLi CRUD
p12_events_helpers.php      Input validation, poster handling and query helpers
css/practical12.css         StudentHub dark style
js/practical12.js           Client-side preview + delete confirmation
practical12.sql             Existing StudentHub upgrade + P12 sample events
Practical12_Full_Install.sql   ONLY for new database installation
uploads/event_posters/      Poster folder (with .htaccess protection)
Practical12-Test-Cases.txt  Test procedure and expected results

Verification note: Programmatic PHP/JS syntax and route checks may be run
before delivery. Actual CRUD, upload, phpMyAdmin import and browser
screenshots MUST be performed on your XAMPP machine.
