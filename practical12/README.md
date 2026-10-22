# Practical 12 — StudentHub Event Management

This corrected ZIP contains **one** project: `practical12/`. There is no nested
`StudentHub/` folder and no separate, competing `event_db` application.

## Installation in XAMPP (existing Practical 11 StudentHub database)

1. Back up the existing `studenthub_db` in phpMyAdmin (**Export**). Do not delete it.
2. Extract the ZIP into `C:\xampp\htdocs\`. You should have:
   - `C:\xampp\htdocs\practical12\login.php`
   - `C:\xampp\htdocs\practical12\event_management.php`
   - `C:\xampp\htdocs\practical12\practical12.sql`
   - `C:\xampp\htdocs\practical12\uploads\event_posters\`
3. Start Apache and MySQL in XAMPP.
4. Open `http://localhost/phpmyadmin/`, select **studenthub_db**,
   then **Import → Choose File → practical12.sql → Go**.
5. Open `http://localhost/practical12/login.php` and log in using your **existing**
   admin credentials (`p10_admin01`, if that is still your username).
6. Open `http://localhost/practical12/event_management.php`.
   `http://localhost/practical12/` also redirects to this page.

**Important:** The migration file upgrades existing tables and inserts example events
without deleting earlier records. Do not import `database.sql` from the separate
standalone app, and do not import `Practical12_Full_Install.sql` when you already have
an existing StudentHub database.

## Report screenshot order

1. phpMyAdmin Practical 12 SQL import.
2. Event Management dashboard, statistics and current events.
3. Add a new event with an actual JPG/PNG/WEBP poster under 2 MiB (example title:
   `Practical 12 Test Workshop`; date `2026-11-26`; category Workshop;
   venue `Computer Lab 3`; status Open).
4. Green success message and uploaded poster image.
5. Edit the test event (venue -> `Seminar Hall B`, status -> Closed).
6. Category/Status filter with matching results.
7. Delete confirmation on the same new test event (only if it has zero registrations).
8. Deletion success message.
9. Reject a `.txt` file uploaded as a poster (no successful image should be saved).
10. phpMyAdmin `event_audit_log` records / error message screenshot (if tested).

Only document tests that actually happened. A >2 MiB upload test is optional.
Please read `README-Practical12.txt` for all checks and troubleshooting.

## Features

Admin-only CRUD with MySQLi prepared queries; file MIME and image-content
validation; maximum 2 MiB; unique image names; upload-folder script blocking;
CSRF protection; search/filter/pagination; event audit entries; safe-delete
protection for events with student registrations.

## Troubleshooting

- 404: verify `C:\xampp\htdocs\practical12\login.php` exists **directly**.
- Logged out: the protected event page redirects to `login.php`.
- Logged in as student: the protected event page returns Access Denied.
- SQL error: select `studenthub_db` and import `practical12.sql`.
- Image upload error: verify XAMPP PHP file uploads and writable
  `uploads/event_posters/` folder; use a real JPEG/PNG/WebP <= 2 MiB.
- If the browser inserted extra characters into a link, manually paste the
  URL into the address bar instead of copying formatted text.

This ZIP's PHP syntax and local route smoke tests are checked before delivery;
real database operations must be verified in your Windows XAMPP installation.
