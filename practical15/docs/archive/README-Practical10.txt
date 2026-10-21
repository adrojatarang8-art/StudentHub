IMPORTANT: Archived practical guide. Current project URL is http://localhost/practical11/ .

STUDENTHUB - PRACTICAL 10
SECURE LOGIN / LOGOUT, SESSIONS, ROLE-BASED ACCESS, TIMEOUT, REMEMBER ME
=====================================================================

1. INSTALLATION / SETUP (XAMPP ON WINDOWS)
-----------------------------------------
A. Extract practical10.zip. Place the StudentHub folder in:
   C:\xampp\htdocs\StudentHub
   Merge it into your existing StudentHub project if needed (back up first).
B. Open XAMPP Control Panel. Start BOTH Apache and MySQL.
C. Open http://localhost/phpmyadmin and select database studenthub_db.
   If the database does not exist, import studenthub_db.sql first.
D. If user_accounts and account_audit_log do not exist, import secure_accounts.sql
   from Practical 9 first. (It does not erase your Practical 8 tables.)
E. Import practical10.sql while studenthub_db is selected.
   This adds role + last_login_at to existing accounts and creates
   remember_tokens. This migration can be re-imported without deleting users.
F. If your XAMPP MySQL has a password, update mysqli_db.php or set DB_PASS.
G. Open http://localhost/StudentHub/login.php

IMPORTANT: Open through http://localhost, not file:/// or VS Code Live Server.
PHP is required to enforce sessions and role-based access.

2. CREATE STUDENT AND ADMIN TEST USERS
--------------------------------------
A. Open http://localhost/StudentHub/secure_account.php
B. Create two accounts using the registration form. For example:
   STUDENT: Full name Demo Student; username student_demo; password Student@123
   ADMIN:   Full name Demo Admin; username admin_demo; password Admin@123
   Use DIFFERENT, valid email addresses and 10-digit test mobile numbers.
   Both accounts are initially STUDENT by design (no user-controlled admin role).
C. In phpMyAdmin -> studenthub_db -> SQL, run ONE of these after creation:

   UPDATE user_accounts SET role='admin' WHERE username='admin_demo';

D. Verify:
   SELECT user_id, username, email, role, last_login_at FROM user_accounts;
E. Sign in at login.php with username/email and the registered password.
   - Student users go to dashboard.php.
   - Admin users go to admin.php.

Do not share real passwords or include them in submission screenshots.

3. PAGES / FILENAMES (CONSISTENT WITH PRACTICAL 9)
-------------------------------------------------
Existing files preserved: mysqli_db.php, secure_account.php,
secure_accounts.sql, db.php, practical8.php, register.php, etc.

NEW Practical 10 files:
  auth.php                      Session settings, CSRF, role checks, timeouts,
                                hashed and rotating remember-me tokens
  login.php                     Real database-backed login using password_verify()
  logout.php                    POST-only secure logout, destroys session/token
  dashboard.php                 STUDENT-only dashboard and logout form
  admin.php                     ADMIN-only dashboard and authentication log
  access_denied.php             403 page for incorrect account role
  practical10.sql               Idempotent MySQL schema upgrade
  README-Practical10.txt        Installation, run and verification instructions
  Practical10-Test-Report.txt   Fill-in report for actual tests performed

UPDATED EXISTING FILES:
  css/style.css                 Matching dark theme styles for Practical 10
  secure_account.php            Adds login navigation and post-register message
  *.html, register.php          Navigation links now point to secured PHP pages
  login.html                    Compatibility redirect to login.php (no fake login)
  dashboard.html                Compatibility redirect to dashboard.php (no data)
  admin.html                    Compatibility redirect to admin.php (no data)

The old .html dashboard pages contain NO confidential data; only PHP pages
serve protected data and require an authenticated, correct-role session.

4. HOW TO TEST THE ASSIGNMENT
----------------------------
  1) Student successful login: enter valid student credentials. Check student
     dashboard and user_accounts.last_login_at. Screenshot Fig 1.
  2) Failed login: wrong password. Should show "Invalid username/email or
     password" and remain on login page. Screenshot Fig 2.
  3) Logout: log in, click "Log out securely" on the dashboard. You return
     to login with "You have logged out successfully". Screenshot Fig 3.
  4) Unauthorized (not logged in): logout; open
     http://localhost/StudentHub/dashboard.php or admin.php directly.
     Must redirect to login.php?status=login_required. Screenshot Fig 4.
  5) Incorrect role (student -> admin): login as student and manually open
     http://localhost/StudentHub/admin.php. It MUST show HTTP 403 Access Denied.
     Screenshot Fig 5 (optional but recommended RBAC proof).
  6) Session timeout: in auth.php, set AUTH_IDLE_TIMEOUT = 60 temporarily.
     Login WITHOUT ticking Remember me, stay inactive for >60 seconds, then
     refresh dashboard.php. It redirects to login.php?status=timeout.
     Screenshot Fig 6. Restore timeout to 900 after test.
  7) Admin login: login with admin_demo after SQL promotion; should open
     admin.php, show account counts and recent audit events. Screenshot Fig 7.
  8) Remember me: login with "Remember me" checked. In browser DevTools
     -> Application -> Cookies, find studenthub_remember, whose cookie has
     HttpOnly and SameSite=Lax. Close browser/reopen after session cookie
     clears to test sign-in restoration. Database remember_tokens stores
     token_hash, NOT the raw cookie validator. Screenshot Fig 8 (optional).

Session idle-timeout tests must be done WITHOUT Remember me, because a valid
remember-me cookie can restore a login in a later browser session.

5. VERIFY IN DATABASE
---------------------
  SELECT username, role, last_login_at FROM user_accounts;
  SELECT action, user_id, created_at FROM account_audit_log
       ORDER BY audit_id DESC LIMIT 20;
  SELECT token_id, user_id, expires_at FROM remember_tokens;

IMPORTANT SECURITY NOTES
------------------------
* Never add role='admin' to the public registration form. Admin is assigned
  only by a trusted database administrator.
* PHP session cookie is HttpOnly / SameSite=Lax; Secure is enabled for HTTPS.
  Local XAMPP http://localhost cannot set Secure cookies; use HTTPS for deployment.
* session_regenerate_id(true) is called after successful login and remember login.
* All protected PHP pages re-check the current role in the database.
* Remember-me uses random selector/validator, stores only SHA-256 token hash,
  rotates token on successful restoration, and deletes current token on logout.
* Login/logout forms use CSRF protection. Logout only accepts POST.
* Response cache is disabled for protected content. Passwords are verified
  with password_verify() against the Practical 9 password_hash() hashes.
* This educational implementation still needs TLS, IP/user-based rate limiting,
  centralized error monitoring, and hardened server settings for production.
* No screenshots or test passes are claimed until YOU run the code in XAMPP.
