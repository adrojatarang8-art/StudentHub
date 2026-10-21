<?php
/** Practical 15 integration homepage. Only verified session roles gain management links. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
auth_initialize();
$member = null;
if (auth_is_signed_in()) {
    try {
        $db = getMySQLi();
        $id = (int)$_SESSION['auth_uid'];
        $stmt = $db->prepare('SELECT full_name,role,status FROM user_accounts WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $candidate = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $db->close();
        if ($candidate && $candidate['status'] === 'active') $member = $candidate;
    } catch (Throwable $error) {
        error_log('P15 home role lookup error: ' . $error->getMessage());
    }
}
$admin = $member && $member['role'] === 'admin';
$student = $member && $member['role'] === 'student';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>StudentHub | Final Integrated Portal</title><meta name="description" content="StudentHub final PHP MySQL project with role-based dashboards and AJAX event management.">
<link rel="stylesheet" href="css/practical15.css"></head><body>
<a href="#main" class="skip">Skip to content</a>
<header class="nav"><div class="container nav-inner"><a class="logo" href="index.php">◈ StudentHub <span>Final Integration</span></a><nav aria-label="Site menu">
<a aria-current="page" href="index.php">Home</a><a href="events.html">Events</a><a href="about.html">About</a>
<?php if ($admin): ?><a href="admin.php">Admin dashboard</a><a href="student_directory.php">Students</a><a href="event_management.php">Posters</a><a href="ajax_events.php">AJAX API</a>
<?php elseif ($student): ?><a href="dashboard.php">Student dashboard</a><?php else: ?><a href="login.php">Login</a><?php endif; ?>
</nav></div></header>
<main id="main" class="container"><section class="hero"><div class="eyebrow">WDF · PRACTICAL 15 · COMPLETE SEMESTER PROJECT</div><h1>One portal. Every module. <span>Ready for local testing.</span></h1>
<p>StudentHub combines registration, secure login, student management, event posters, PHP JSON APIs and an audited admin dashboard.</p>
<div class="hero-actions">
<?php if ($admin): ?><a class="btn primary" href="admin.php">Open admin dashboard →</a><a class="btn secondary" href="ajax_events.php">Open AJAX CRUD</a>
<?php elseif ($student): ?><a class="btn primary" href="dashboard.php">Open my dashboard →</a><a class="btn secondary" href="events.html">View events</a>
<?php else: ?><a class="btn primary" href="login.php">Secure login →</a><a class="btn secondary" href="secure_account.php">Create account</a><?php endif; ?>
</div>
<p class="welcome"><?php if ($member): ?>Signed in as <strong><?=auth_e($member['full_name'])?></strong> (<?=auth_e($member['role'])?>).<?php else: ?>Guest view · Admin actions require an administrator account.<?php endif; ?></p></section>
<section class="section" aria-label="Project modules"><div class="sectionhead"><div><div class="eyebrow">INTEGRATED MODULES</div><h2>Project navigation</h2></div><p>Live PHP pages, original HTML exercises and documented installation.</p></div>
<div class="cards">
<a class="tile" href="register.php"><b>01</b><strong>Form Registration</strong><span>POST validation, CSV/JSON storage and feedback</span></a>
<a class="tile" href="practical8.php"><b>02</b><strong>MySQL Integration</strong><span>Prepared statement registration and database operations</span></a>
<a class="tile" href="login.php"><b>03</b><strong>Secure Authentication</strong><span>Sessions, role checks, remember-me and logout</span></a>
<?php if ($admin): ?>
<a class="tile" href="student_directory.php"><b>04</b><strong>Student CRUD</strong><span>Search, filter, export and soft delete</span></a>
<a class="tile" href="event_management.php"><b>05</b><strong>Event Posters</strong><span>Validated JPG / PNG / WEBP upload and event status</span></a>
<a class="tile" href="ajax_events.php"><b>06</b><strong>AJAX Event CRUD</strong><span>Fetch JSON API with live search and error states</span></a>
<a class="tile" href="admin.php"><b>07</b><strong>RBAC Admin Dashboard</strong><span>Database statistics, registrations, audit logs and chart</span></a>
<?php else: ?>
<a class="tile" href="events.html"><b>04</b><strong>Browse Events</strong><span>Public event information and student resources</span></a>
<a class="tile" href="login.php"><b>05</b><strong>Admin-only Tools</strong><span>Sign in as an administrator to access CRUD and audit logs</span></a>
<?php endif; ?>
<a class="tile" href="timetable.html"><b>08</b><strong>Student Resources</strong><span>Timetable, attendance, profile and public pages</span></a>
<a class="tile" href="docs/TEST-REPORT.md"><b>09</b><strong>Final Documentation</strong><span>Functional test matrix, security checks and evidence guide</span></a>
</div></section>
<section class="footnote"><h2>Local XAMPP deployment</h2><p>For installation, import <code>practical15.sql</code> into the existing <code>studenthub_db</code> and visit <code>http://localhost/practical15/</code>. The optional Apache Virtual Host is documented in <code>DEPLOYMENT.md</code>.</p><a href="README.md">Read project instructions →</a></section>
</main><footer class="container footer">StudentHub · WDF Practical 15 · PHP 8.0+ / MySQL / JavaScript · Local demo</footer></body></html>
