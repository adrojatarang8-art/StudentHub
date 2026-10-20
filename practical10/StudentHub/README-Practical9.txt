STUDENTHUB – SECURE ACCOUNT REGISTRATION (PRACTICAL 9)

1. Extract ZIP to C:\xampp\htdocs\StudentHub (or merge with your existing StudentHub folder).
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin and select EXISTING database studenthub_db.
4. Import secure_accounts.sql (DO NOT delete or replace students/events/registrations).
5. Open http://localhost/StudentHub/secure_account.php
6. Valid test: Example Student / student_test1 / student_test1@example.com / 9876543210 / Example@123 -> successful creation.
7. Duplicate email: retry with same email and another username -> email already registered.
8. Duplicate username: retry with same username and another email -> username already taken.
9. Invalid: try abc (weak password) or 123 (bad email) -> validation error.
10. In phpMyAdmin select user_accounts > Browse. Password_hash must contain a long password hash, NOT plain password.
11. In phpMyAdmin check account_audit_log for account_registered activity.
12. For submission capture form, success, validation error, duplicate email, duplicate username, user_accounts hash and audit log screenshots.

FILES: mysqli_db.php, secure_accounts.sql, secure_account.php, js/secureAccount.js, Practical9-Test-Report.txt
Practical 7 CSV/JSON & Practical 8 PDO event registrations remain available separately.
NOTE: Creating an account does not yet log the user in: login.html remains a demo UI and is not wired to password_verify().
For production, enable HTTPS, rate limiting and secure database credentials/environment variables.
