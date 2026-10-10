StudentHub - Practical 7
========================

PRACTICAL 7
-----------
PHP Form Processing with Server-Side Validation and CSV/JSON File Storage

PROBLEM DEFINITION
------------------
Process submitted registration/contact form data using PHP. Validate and sanitize inputs on the server side and store records in CSV or JSON file format. Display success/error messages.

WHAT WAS ADDED
--------------
1. register.php - existing registration form connected to PHP using method="POST".
2. process_registration.php - POST handling, sanitization, server-side validation and storage.
3. data/registrations.csv - CSV storage file. New records are appended safely.
4. data/registrations.json - JSON storage file. New records are appended safely.
5. records.php - displays stored JSON records in a table (Intermediate extension).
6. CSRF token validation using PHP sessions (Advanced extension).
7. css/style.css - Practical 7 result, message and records-table styles.
8. script.js - existing Practical 5 client validation now submits valid data to PHP.

KEY QUESTIONS / SHORT ANSWERS
-----------------------------
Q1. Is the form submitted using POST?
A. Yes. register.php uses method="POST" and sends the form to process_registration.php.

Q2. Are inputs validated and sanitized server side?
A. Yes. PHP trims/removes unsafe tags, validates email/mobile/name/password and checks allowed course/year/gender values.

Q3. Is file writing handled safely?
A. Yes. CSV and JSON files are opened carefully and flock() is used to lock each file while writing.

Q4. Are success and error responses displayed clearly?
A. Yes. The processor displays clear success, validation, security and storage-error messages.

KEY SKILLS
----------
- PHP forms
- POST handling
- Server-side validation
- Sanitization
- File writing
- CSV storage
- JSON storage
- File locking
- Password hashing
- CSRF token validation

HOW TO RUN WITH XAMPP
---------------------
1. Copy StudentHub_Practical7 folder into:
   C:\xampp\htdocs\
2. Start Apache from XAMPP Control Panel.
3. Open this URL in browser:
   http://localhost/StudentHub_Practical7/register.php
4. Fill the registration form and click Register.
5. After success, click "View Stored Records".
6. CSV data is in data/registrations.csv.
7. JSON data is in data/registrations.json.

IMPORTANT
---------
Do NOT run register.php by double-clicking the file.
PHP needs Apache/XAMPP (or another PHP server).

TEST CASES
----------
TC1: Valid data -> Success; record appears in CSV + JSON.
TC2: Empty name -> Server returns name-required error.
TC3: Invalid email -> Server returns invalid-email error.
TC4: Mobile with fewer than 10 digits -> Validation error.
TC5: Weak password -> Password validation error.
TC6: Confirm password mismatch -> Validation error.
TC7: Course/year/gender missing -> Validation error.
TC8: Terms not accepted -> Validation error.
TC9: Direct GET request to process_registration.php -> Invalid Request / POST required.
TC10: Invalid CSRF token -> Security Check Failed.

LEARNING OUTCOME
----------------
Students will implement server-side form processing with safe file-based storage.

CO MAPPING
----------
CO1, CO5

TOOLS / TECHNOLOGY
------------------
PHP, XAMPP/WAMP/LAMP, HTML, CSS, JavaScript, browser DevTools

EXTENSIONS COMPLETED
--------------------
Intermediate: Display stored CSV/JSON records on a webpage -> records.php.
Advanced: CSRF token validation for form submission -> implemented with PHP session token.
