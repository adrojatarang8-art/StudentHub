<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/p14_nav.php';
$user = auth_require_role('student');
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>StudentHub | Student Dashboard</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/practical14.css"></head>
<body class="p10-body"><header class="p10-header"><h1>StudentHub · Student Dashboard</h1><?php p14_nav($user, 'dashboard.php'); ?></header>
<main class="p10-dashboard"><section class="p10-wide"><div class="p10-eyebrow">STUDENT ACCOUNT · SECURE SESSION</div><h2>Welcome, <?=auth_e($user['full_name'])?>!</h2><p class="p10-muted">You are authenticated as a <strong>student</strong>. Only your account details are shown below.</p><div class="p10-stats">
<div class="p10-stat"><span>Username</span><strong><?=auth_e($user['username'])?></strong></div>
<div class="p10-stat"><span>Email</span><strong><?=auth_e($user['email'])?></strong></div>
<div class="p10-stat"><span>Role</span><strong>Student</strong></div>
<div class="p10-stat"><span>Last login</span><strong><?=auth_e($user['last_login_at'] ?? 'Not recorded')?></strong></div></div>
<h3>Student resources</h3><div class="p10-links"><a href="events.html">View Events →</a><a href="timetable.html">View Timetable →</a><a href="attendance.html">Attendance →</a><a href="profile.html">Profile →</a></div>
<form action="logout.php" method="POST"><input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>"><button type="submit" class="p10-logout">Log out securely</button></form>
<p class="p10-muted p10-small">Your session automatically expires after 15 minutes of inactivity.</p></section></main><footer><p>© 2026 StudentHub Portal</p></footer></body></html>
