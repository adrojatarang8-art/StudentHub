STUDENTHUB | PRACTICAL 13: PHP JSON API + AJAX/FETCH-BASED CRUD
=============================================================
Uses the existing Practical 11/10 StudentHub project, PHP + MySQLi + Fetch.
ADMIN ONLY. All event operations are asynchronous: page does not reload.

A. WHAT IS INCLUDED / FILE NAMES
--------------------------------
ajax_events.php            Protected dark theme AJAX event management page
api/bootstrap.php          Shared JSON responses / session / role / CSRF /
                           validation / error handler / HTTP status codes
api/events_list.php        GET: list/search/filter events
api/event_stats.php        GET: total and upcoming event counts
api/events_create.php      POST: create event with JSON body
api/events_update.php      POST: update event with JSON body and event_id
api/events_delete.php      POST: delete test event using JSON event_id
js/practical13-events.js   Fetch client, debounce (300ms), CRUD, UI messages
css/practical13.css        Responsive dark theme, matching Practical 11
practical13.sql            Non-destructive existing-DB upgrade + five test events
Practical13_Full_Install.sql   FRESH database only: P8-11 setup + P13 data
Practical13-Test-Cases.txt    Step-by-step tests, screenshot checklist, viva
START_HERE.txt             Quick installation guide
Prior Practical 7-11 StudentHub files are also preserved in this ZIP.

B. INSTALL ON EXISTING STUDENTHUB_DB (RECOMMENDED)
-----------------------------------------------
1. Extract practical13.zip in C:\xampp\htdocs\ .
   Final folder MUST be C:\xampp\htdocs\practical13\ .
   Do NOT extract into practical13\practical13 or practical11\practical13.
2. Start Apache AND MySQL in the XAMPP Control Panel.
3. Browse http://localhost/phpmyadmin/ .
4. Select existing studenthub_db -> Import -> Choose practical13.sql -> Go.
   This SQL does not DROP or DELETE your old records and is re-importable.
   Practical 13 uses the existing `events` table from Practical 8.
5. Open http://localhost/practical13/login.php .
6. Sign in with your EXISTING administrator username and password.
   If your admin username is p10_admin01, reuse THAT saved password.
7. Open http://localhost/practical13/ajax_events.php .
   Or on the Admin Dashboard click AJAX CRUD (P13).
8. The API calls automatically load event rows and stats via Fetch.

C. BRAND NEW INSTALL (NO studenthub_db YET)
-------------------------------------------
1. Import ONLY Practical13_Full_Install.sql in phpMyAdmin.
2. Create an account at http://localhost/practical13/secure_account.php .
3. In phpMyAdmin -> studenthub_db -> SQL, assign admin to your own account:
   UPDATE user_accounts SET role='admin' WHERE username='YOUR_USERNAME';
4. Login via login.php and open ajax_events.php .
   No insecure default admin/password has been added to the project.

D. HOW TO DEMONSTRATE CRUD + CAPTURE SCREENSHOTS
------------------------------------------------
1. Open ajax_events.php; check loaded records / stats, screenshot.
2. F12 -> Network -> Fetch/XHR; press Refresh List. Take screenshot of
   events_list.php GET request (Status 200) and its JSON Response/Preview.
3. Add event:
   Title: Practical 13 Live AJAX Demo
   Date: 2026-11-25
   Category: Workshop
   Venue: Computer Lab 2
   Description: Demonstrating asynchronous CRUD using PHP and Fetch API.
   Click Add Event. Green success and new record appear NO page reload.
   F12 Network POST events_create.php (Status 201), JSON success true.
4. Click Edit for YOUR newly created event; change venue to Lab 3, Save.
   F12 Network POST events_update.php (Status 200).
5. Search title 'Live AJAX Demo'. Type in Search field: waits 300ms then
   GET request automatically. Search needs NO button, NO reload.
6. Reset search; click Delete for YOUR demo event; confirm. New events
   have no registrations, so delete works. Inspect POST events_delete.php.
   Old events with registrations are protected (409 Conflict), not deleted.
7. Leave title empty or use a 1-character description to test input
   validation. Frontend blocks malformed input. For API-side validation,
   inspect endpoint via a browser API client with a logged-in session.
8. View the loading indicator and network status shown on the page. Check
   invalid session in a separate browser / after logout (401 JSON).

E. ENDPOINT REFERENCE
--------------------
GET api/events_list.php?q=workshop&category=Workshop
  {"success":true,"message":"Events loaded successfully.",
   "data":[{"event_id":...,"title":...,"event_date":...}],
   "meta":{"shown":...,"total":...,"limit":250}}

GET api/event_stats.php
  {"success":true,"data":{"total":...,"upcoming":...}}

POST api/events_create.php
  Content-Type: application/json
  X-CSRF-Token: value embedded in ajax_events.php meta tag
  {"title":"My Event","event_date":"2026-11-25","venue":"Lab 1",
   "category":"Workshop","description":"API-based AJAX event demo"}
  Returns HTTP 201 and {"success":true,"message":...,"data":{"event_id":...}}

POST api/events_update.php
  Same JSON as create + "event_id": 123; returns HTTP 200.

POST api/events_delete.php
  {"event_id":123}; returns HTTP 200 or 404/409.

Statuses: 200 GET/Update/Delete | 201 Create | 400 Malformed JSON |
401 Not logged in | 403 Invalid CSRF/not Admin | 404 Missing ID |
405 Wrong request method | 409 Event with linked registrations |
415 Wrong Content-Type | 422 Invalid data | 500/503 Database errors.
Response on error:
  {"success":false,"error":{"code":"...","message":"..."}}

F. SECURITY & DATABASE SAFETY
----------------------------
- All SQL values go through MySQLi prepared statements.
- Session authentication and role check come from the shared StudentHub
  Practical 10 backend and database user_accounts table.
- All write endpoints require a valid CSRF header plus JSON body.
- No cross-origin API access enabled; use localhost site after login.
- Fields validated in browser AND API before database writes.
- JSON errors do not reveal SQL connection details to the browser.
- Delete refuses events with registrations; linked student data kept safe.
- Only test newly created events for delete to avoid unwanted data loss.
- No JavaScript location.reload() or ordinary form submission in AJAX UI.
- Event GET response capped to 250 rows for a simple classroom demo.

G. IMPORTANT / TROUBLESHOOTING
-------------------------------
404 -> Check C:\xampp\htdocs\practical13\ajax_events.php and start Apache.
Login page -> Sign in with ADMIN; student accounts cannot access this API.
Database failure -> Start MySQL, import practical13.sql and check mysqli_db.php.
Blank loading/error -> F12 Console + Network. PHP must be served by Apache,
not opened as a local file or VS Code Live Server.
StudentHub older P11's optional soft-delete status mismatch is unrelated to
Practical 13 events CRUD; this new module uses the events table.

H. REPORT & SCREENSHOT REQUIREMENTS
----------------------------------
Capture real, unedited screenshots. No example screenshots are asserted as
actual tests in this ZIP. Use Practical13-Test-Cases.txt.
Same Word report structure as Practical 10/11 when screenshots are supplied:
Theory -> Result -> Evidence of Work -> GitHub -> Challenges -> Learning
Outcome -> Faculty Remarks; header 'Department of Computer Engineering',
Student ID 25DCE001, aligned screenshots and labeled figures.
