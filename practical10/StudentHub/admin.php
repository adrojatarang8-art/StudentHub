<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$user = auth_require_role('admin');
$total = 0; $students = 0; $admins = 0; $recent = [];
try {
    $db = getMySQLi();
    $counts = $db->query("SELECT COUNT(*) AS total,
        SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) AS students,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admins FROM user_accounts")->fetch_assoc();
    $total = (int)$counts['total']; $students = (int)$counts['students']; $admins = (int)$counts['admins'];
    $list = $db->query('SELECT ua.username, al.action, al.created_at FROM account_audit_log al
                        LEFT JOIN user_accounts ua ON al.user_id = ua.user_id
                        ORDER BY al.audit_id DESC LIMIT 8');
    $recent = $list->fetch_all(MYSQLI_ASSOC);
    $db->close();
} catch (Throwable $e) {
    error_log('Admin dashboard stats: ' . $e->getMessage());
    http_response_code(503);
    exit('Dashboard statistics are unavailable.');
}
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>StudentHub | Admin Dashboard</title><link rel="stylesheet" href="css/style.css"></head>
<body class="p10-body"><header class="p10-header"><h1>StudentHub · Admin Dashboard</h1><nav><a href="index.html">Home</a><a href="admin.php" aria-current="page">Admin Dashboard</a><a href="events.html">Events</a></nav></header>
<main class="p10-dashboard"><section class="p10-wide"><div class="p10-eyebrow">ADMINISTRATOR · RESTRICTED AREA</div><h2>Welcome, <?=auth_e($user['full_name'])?>!</h2><p class="p10-muted">Role verified by the database. Students cannot access this page.</p><div class="p10-stats">
<div class="p10-stat"><span>Total Accounts</span><strong><?=auth_e($total)?></strong></div>
<div class="p10-stat"><span>Student Accounts</span><strong><?=auth_e($students)?></strong></div>
<div class="p10-stat"><span>Administrators</span><strong><?=auth_e($admins)?></strong></div>
<div class="p10-stat"><span>Last Login</span><strong><?=auth_e($user['last_login_at'] ?? 'Not recorded')?></strong></div></div>
<h3>Recent authentication activity</h3><div class="p10-tablewrap"><table><thead><tr><th>Username</th><th>Event</th><th>Time</th></tr></thead><tbody>
<?php if (!$recent): ?><tr><td colspan="3">No authentication activity recorded yet.</td></tr><?php else: ?><?php foreach ($recent as $item): ?><tr><td><?=auth_e($item['username'] ?? '(unknown)')?></td><td><?=auth_e($item['action'])?></td><td><?=auth_e($item['created_at'])?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div>
<form action="logout.php" method="POST"><input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>"><button type="submit" class="p10-logout">Log out securely</button></form>
</section></main><footer><p>© 2026 StudentHub Portal</p></footer></body></html>
