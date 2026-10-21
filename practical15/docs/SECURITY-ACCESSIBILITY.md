# Practical 15 — Security & Accessibility Checklist

## Controls present in source (needs XAMPP verification)

- `auth.php`: sessions, `session_regenerate_id()` at sign-in, inactivity timeout, hashed password check, role/status refreshed from MySQL on restricted pages.
- `admin.php`, `account_status.php`, student/event CRUD and JSON APIs enforce server-side admin role. Merely hiding links is **not** sufficient.
- State-changing admin forms require CSRF token; JSON API sends `X-CSRF-Token` and checks it.
- MySQL operations use MySQLi prepared statements for user input; `db.php` PDO connection uses non-emulated prepares.
- Output is escaped before HTML insertion in PHP templates; DOM code should be manually checked for unescaped HTML.
- `event_management.php` validates MIME, image dimensions, 2MB upload limit and randomized stored filenames. Apache denies execution of code files inside poster upload folder.
- Admin actions are tracked in `audit_logs`; Practical12 additionally writes `event_audit_log`.
- `data/.htaccess` denies HTTP access to form records; `.gitignore` excludes actual CSV, JSON submissions and uploaded images.
- No hardcoded administrator password. `.env.example` is only an example; PHP reads `DB_*` from environment (does not parse `.env`).

## Manual security tests — leave unchecked until tested

- [ ] Student account receives HTTP 403 on admin dashboard, poster CRUD and AJAX POST API.
- [ ] Logged-out admin redirects to login and API returns JSON 401.
- [ ] Invalid CSRF token is refused without DB mutation.
- [ ] SQL injection attempt stays data and does not alter SQL query structure.
- [ ] Invalid JPG/PNG/WEBP upload content (e.g., fake `.jpg` text) is rejected; large file test optional if not performed.
- [ ] Logout prevents protected navigation; session timeout is tested using real passage of time.
- [ ] Reactivate any lab test account after the inactive toggle test.
- [ ] Test malicious upload file retrieval never runs PHP/JS.

## Manual accessibility tests

- [ ] All input controls have accessible labels; errors are descriptive and attached to the form.
- [ ] Keyboard Tab moves through links/forms; focus visible; skip link works on portal home.
- [ ] Images used in content include meaningful alt text; decorations use empty alt as appropriate.
- [ ] Layout tested at widths 375px, 768px and 1366px without content loss or horizontal overflow.
- [ ] Text contrast, headings and table readability checked with a real browser or accessibility tools.

## Deployment cautions

- XAMPP development MySQL `root` account and blank password are **only for localhost**.
- HTTPS is mandatory for public deployment; enable secure cookies and proper host-specific secrets.
- There is no built-in rate limiter. Add login attempt throttling and monitor failed attempts before Internet exposure.
- Database backups, CSRF boundary, upload content sanitization and other security hardening must be reviewed before actual production usage. This is a **semester lab project**, not a production security certification.
