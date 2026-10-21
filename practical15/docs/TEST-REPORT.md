# Practical 15 — Integrated Test Report (Fill after XAMPP test)

**Environment:** Windows / XAMPP Apache / PHP ____ / MySQL/MariaDB ____ / Chrome ____  
**Test date:** __________ · **Tester:** __________  
**Project URL:** http://localhost/practical15/  
**Database:** studenthub_db

Do **not** mark PASS just because a page exists in source. Record actual Chrome / phpMyAdmin evidence.

| ID | Area | Steps | Expected result | Actual / status | Screenshot |
|---|---|---|---|---|---|
| T01 | Installation | Import `practical15.sql` into existing `studenthub_db` | Tables preserved, migration succeeds | NOT RUN | 01-sql-import.png |
| T02 | Navigation | Open homepage and follow Home, Login, Events | No broken links | NOT RUN | 02-home.png |
| T03 | Admin sign-in | Login with authorized admin | Admin dashboard opens | NOT RUN | 03-admin-login.png |
| T04 | Live stats | Compare SQL counts with dashboard | Account, student, event, registration counts match | NOT RUN | 04-dashboard.png |
| T05 | RBAC | Student opens `admin.php`, `event_management.php` | 403 access denied | NOT RUN | 05-rbac-denied.png |
| T06 | Dynamic menu | Compare logged-in admin and student nav | Only allowed menu items appear for each role | NOT RUN | 06-role-nav.png |
| T07 | Student CRUD | Add, edit, search; soft-delete test row | Saved MySQL state and UI reflect changes | NOT RUN | 07-student-crud.png |
| T08 | Poster CRUD | Upload valid JPG/PNG, update event, delete disposable event | Upload saved; poster preview and CRUD work | NOT RUN | 08-poster-upload.png |
| T09 | Upload validation | Attempt non-image text file renamed `.jpg` | Rejected with validation message | NOT RUN | 09-poster-invalid.png |
| T10 | AJAX GET | F12 Fetch/XHR → Refresh List → Response | JSON object, success:true, HTTP 200 | NOT RUN | 10-api-get.png |
| T11 | AJAX POST | Add/edit/delete disposable event | No full reload, appropriate response/notice | NOT RUN | 11-ajax-post.png |
| T12 | AJAX search | Type title | Debounced filtered list updates | NOT RUN | 12-ajax-search.png |
| T13 | Validation | Submit invalid/empty input | Clear error, no DB modification | NOT RUN | 13-form-validation.png |
| T14 | Audit | Make authorized admin changes and query audit_logs | User, action, entity, timestamp recorded | NOT RUN | 14-audit.png |
| T15 | User status | Deactivate/reactivate test student account | Account access blocked/re-enabled, action audited | NOT RUN | 15-account-status.png |
| T16 | Responsiveness | Resize 375px / 768px / desktop | No hidden essential controls | NOT RUN | 16-responsive.png |
| T17 | Session | Logout, then open protected page | Login required; session expired path tested if possible | NOT RUN | 17-logout.png |
| T18 | Local deployment | Open standard / optional VHost URL | Same application and DB connection | NOT RUN | 18-deployment.png |
| T19 | GitHub | Push repository and open URL | README visible; no secrets committed | NOT RUN | 19-github.png |

## Data integrity queries

```sql
SELECT COUNT(*) AS students FROM students;
SELECT COUNT(*) AS events FROM events;
SELECT COUNT(*) AS registrations FROM registrations;
SELECT audit_id, admin_user_id, action, entity_type, entity_id, created_at
FROM audit_logs ORDER BY audit_id DESC LIMIT 20;
```

## Screenshots and notes

Place screenshots in `screenshots/` after capturing real results. Write the observed pass/fail reason for any issues. GitHub screenshots are optional if they contain private data: redact safely. Report only tests actually performed.

**Open issues found:** _______________________________________________

**Conclusion (complete only after testing):** __________________________
