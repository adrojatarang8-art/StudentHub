# StudentHub — WDF Practical 15 (Full Integration)

**Local PHP 8.0+ / Apache / MySQL StudentHub project.** Includes earlier Practical 7–14 modules, Practical 12 poster-upload event manager, and a new responsive integrated homepage. **This is source + schema + test instructions, not proof of a completed deployment or GitHub push.**

## Quick start — existing Practical 14 setup

1. Back up your database first: `http://localhost/phpmyadmin/` → select `studenthub_db` → **Export → Quick → SQL → Go**. Save the backup outside public `htdocs`.
2. Extract `practical15.zip` directly into `C:\xampp\htdocs\`. Confirm `C:\xampp\htdocs\practical15\index.php` exists — **not** `practical15\practical15\index.php`.
3. Start **Apache** and **MySQL** in XAMPP.
4. Select `studenthub_db` → **Import** → choose `C:\xampp\htdocs\practical15\practical15.sql` → **Go**. This safely adds missing poster/event audit fields and rechecks the Practical 14 audit schema. It does not delete earlier rows.
5. Visit **http://localhost/practical15/** or **http://localhost/practical15/login.php** (copy URLs without `**`). Log in using your existing admin identity/password (e.g. `p10_admin01` only if that account exists). There is **no built-in password**.
6. Admin tools: `admin.php`, `student_directory.php`, `event_management.php`, `ajax_events.php`; Student dashboard: `dashboard.php` (requires student role).

**New database instead?** Import **`sql/Practical15_Fresh_Install.sql`** (not `practical15.sql`) into phpMyAdmin. It recreates the schema with sample rows. Create your own account at `secure_account.php`, then for **local lab use only** assign the intended user admin role in `user_accounts` (no default admin password is shipped).

## Modules & routes

| Area | URL / file | Description |
|---|---|---|
| Portal | `/practical15/` (`index.php`) | Responsive final navigation and role-aware links |
| Forms | `register.php`, `records.php` | Validated submissions; stored record view is admin-only |
| PDO + registrations | `db_test.php`, `practical8.php` | Admin-only database test and event registration example |
| Authentication | `secure_account.php`, `login.php`, `logout.php` | Registration, role checks, hashed passwords, sessions, idle timeout |
| Student management | `student_directory.php`, `student_export.php` | Admin CRUD, search, filter, pagination and CSV export |
| Poster event management | `event_management.php` | Admin CRUD, event categories/status, optional validated poster upload (max 2 MB) |
| AJAX event management | `ajax_events.php`, `api/events_*.php` | JS Fetch GET/POST JSON CRUD without page reload, debounced search |
| Admin control panel | `admin.php`, `account_status.php` | Live SQL stats, registrations, monthly chart, RBAC, account toggle and audit trail |
| Static student pages | `events.html`, `timetable.html`, `attendance.html`, `profile.html` | Existing HTML/CSS/JS coursework pages |

**Note:** The Practical 12 and Practical 13 event modules use the same `events` table. Poster fields are stored only when using `event_management.php`; AJAX CRUD does not upload posters. Both record admin changes in `audit_logs` when the Practical 15 migration is imported.

## Final submission files

- `practical15.sql` — existing DB migration. **Not a dump of your actual database**.
- `sql/Practical15_Fresh_Install.sql` — new DB schema and synthetic seed SQL.
- `docs/TEST-REPORT.md` — test cases, expected results, screenshot names and blank actual evidence fields.
- `docs/SECURITY-ACCESSIBILITY.md` — audit of implemented controls and remaining manual checks.
- `DEPLOYMENT.md` — XAMPP and optional Virtual Host steps.
- `GITHUB_SUBMISSION.md` — Git commands, clean repo checklist, repository URL field.
- `docs/Practical12-Guide.txt`, `docs/archive/` — historical practical guides.

## Verify at localhost

- Admin account opens `admin.php` (data values should match phpMyAdmin). Student role must get **403** on admin pages (record screenshot).
- Add a disposable student / poster event / AJAX event; edit and delete only disposable unregistered rows. Verify audit entries in `audit_logs` and the relevant page success message. Poster uploads: valid JPG/PNG/WEBP under 2 MB; invalid type must be rejected.
- Use F12 → Network → Fetch/XHR to capture GET response JSON `success:true`, POST HTTP 201 and AJAX error states.
- Test at a phone-sized window, keyboard navigation, labels, focus, readable contrast and failed forms.
- Test idle timeout and logout redirection manually.

> Source syntax and route checks are possible offline; a full pass depends on **your** running XAMPP, MySQL state, chosen credentials, browser and actual screenshots. Do not mark checks PASS until tested.

## Troubleshooting

**404** — Confirm the file exists directly in `htdocs/practical15`; wrong double-folder extraction is common. **403** — Requires admin role on protected URLs, or inspect Apache configuration for server-level denial. **DB error** — Start MySQL and import the right SQL; keep old `studenthub_db`. **API POST fails** — Check F12 → Network → Response, PHP version and the session cookie; JSON endpoints use PHP 8.0-compatible validation. **Poster upload fails** — Check `fileinfo`, `uploads/event_posters` writable and PHP upload limits.

**Local development only:** XAMPP's default `root` blank password is not appropriate for online hosting. See `docs/SECURITY-ACCESSIBILITY.md` before any Internet exposure.
