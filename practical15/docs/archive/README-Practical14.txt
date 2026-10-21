PRACTICAL 14 — ADMIN DASHBOARD WITH ROLE-BASED ACCESS, DYNAMIC MENU & AUDIT LOG
============================================================================
Tested design target: XAMPP Apache + PHP 8.0.30 + MySQL/MariaDB, JavaScript.
Project folder: practical14 (NO extra project folder inside).
Project continues Practical 13 PHP-8.0-compatible StudentHub features.

QUICK INSTALL (USING YOUR EXISTING PRACTICAL 13 DATABASE)
---------------------------------------------------------
1) Back up C:\xampp\htdocs\practical13 and export studenthub_db if desired.
2) Extract ZIP INTO C:\xampp\htdocs\. The resulting layout must be:
      C:\xampp\htdocs\practical14\login.php
      C:\xampp\htdocs\practical14\admin.php
      C:\xampp\htdocs\practical14\practical14.sql
   Do NOT put practical14 inside another practical14 folder.
3) Start XAMPP -> Apache and MySQL.
4) Open http://localhost/phpmyadmin/ -> select studenthub_db -> Import.
   Choose C:\xampp\htdocs\practical14\practical14.sql and click Go.
   This safely adds user_accounts.status and audit_logs without dropping tables.
   Do NOT run Practical14_Full_Install.sql on an existing setup.
5) Visit http://localhost/practical14/login.php (type directly in address bar).
   Log in with existing admin username p10_admin01 and the password you chose.
6) Open http://localhost/practical14/admin.php
   or http://localhost/practical14/admin_dashboard.php.
7) No new SQL import is required for existing Practical 13 API endpoints.

FEATURES
--------
- Five live metric cards: total accounts, student table rows, events, registrations,
  and active/inactive user accounts. Queries use MySQLi and the existing tables.
- Recent registrations: student, enrolled code, event title, registration date.
- Role-based navigation: admins see management links; student dashboard sees
  student-only navigation. Every restricted page performs server-side role checks.
- Admin audit_logs: CREATE/UPDATE/DELETE event via Practical 13 AJAX API,
  CREATE/UPDATE/soft-delete/restore via Practical 11 student page, and
  activate/deactivate user_accounts.
- Account toggle: administrator-only POST, CSRF protected and logged in audit_logs.
  Self-deactivation and deactivation of final active admin blocked.
  Inactive accounts cannot sign in; existing role-protected sessions stop working.
  Remember-me tokens are revoked when an account is deactivated.
- Monthly registration bar chart: six calendar months using registrations.registered_at.
- Dashboard dark mode, responsive layout, clear messages.
- Existing P13 AJAX /api and P11 student CRUD retained.

BE AWARE
--------
The login activity from Practical 10 remains in account_audit_log.
Practical 14 administrative actions are instead in a new audit_logs table.
Do not confuse the two separate tables.

SCREENSHOT WALKTHROUGH FOR PRACTICAL-14 REPORT
---------------------------------------------
01. phpMyAdmin Import practical14.sql result and audit_logs table existing.
02. Admin dashboard summary showing live counts and registration chart.
03. Latest registrations table and chart.
04. Admin role menu including management entries.
05. Login as student p10_student01 and open /practical14/admin.php.
    Expected 403 Access Denied (DO NOT take account screenshots with secrets).
06. Login again as admin; locate p10_student01 under User account management.
07. Deactivate test STUDENT account, note success; screenshot changed status.
08. View audit_logs in dashboard or phpMyAdmin (account_deactivated entry).
09. Reactivate same test account so other practicals still work.
10. Create a NEW disposable test event in /practical14/ajax_events.php.
    Screenshot green success, then see event_created audit row in admin.php.
11. Edit and delete only that new unregistered test event; verify updated audit rows.
12. Logout and show admin page returns to login page.

SAMPLE SQL FOR SCREENSHOT (read only)
-----------------------------------
SELECT user_id,username,role,status FROM user_accounts ORDER BY user_id;
SELECT audit_id,action,entity_type,entity_id,details,created_at
  FROM audit_logs ORDER BY audit_id DESC LIMIT 15;
SELECT COUNT(*) AS total_students FROM students;
SELECT COUNT(*) AS total_events FROM events;
SELECT COUNT(*) AS total_registrations FROM registrations;

FILES
-----
admin.php                 P14 main dashboard + latest registrations + bar chart + logs
admin_dashboard.php       Alias that redirects to admin.php (after RBAC check)
account_status.php        Safe and audited active/inactive toggle
p14_nav.php               Role-based menu
p14_audit.php             Shared audit insertion helper
practical14.sql           Existing-database, repeatable SQL upgrade
css/practical14.css       Responsive dark theme
api/events_*.php          Existing Practical 13 JSON CRUD + P14 audit writes
student_directory.php     Practical 11 CRUD + P14 audit writes
auth.php, login.php       Login + role/status enforcement + 15m timeout
Practical14-Test-Cases.txt  Screenshot checklist and viva evidence

TROUBLESHOOTING
---------------
404: Check URL is http://localhost/practical14/admin.php (no extra ** characters)
     and that C:\xampp\htdocs\practical14\admin.php exists.
403: Student accounts should be denied by design. Login with an admin account.
503: Confirm you imported practical14.sql into studenthub_db (not another DB).
     Confirm Apache/MySQL started in XAMPP.
No audit rows: perform a test admin mutation after importing practical14.sql.
Cannot test admin permissions without user accounts: register a test account
     via secure_account.php and give it admin role using phpMyAdmin in a lab
     environment; never publish example credentials.

IMPORTANT: The ZIP was syntax/structure-checked outside your Windows XAMPP.
Actual live SQL import, role login, CRUD and UI screenshots must be checked
in your own local setup. Do not claim tests ran if not personally tested.
