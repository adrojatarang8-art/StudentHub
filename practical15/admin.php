<?php
/** Practical 14 – Live Admin Dashboard, RBAC, recent registrations, audit trail. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/p14_nav.php';
$admin = auth_require_role('admin'); // verifies session AND fresh role/status from MySQL
$accounts = $studentCount = $eventCount = $registrationCount = $activeAccounts = $inactiveAccounts = $adminCount = 0;
$latest = $auditRows = $users = [];
$monthly = [];
$error = '';
$notice = [
  'inactive' => 'Account disabled successfully. Active sessions and remember-me tokens are no longer usable.',
  'active' => 'Account enabled successfully.',
  'unchanged' => 'No change was needed.',
][(string)($_GET['notice'] ?? '')] ?? '';
try {
    $db = getMySQLi();
    $stats = $db->query("SELECT COUNT(*) AS total,
      COALESCE(SUM(status = 'active'),0) AS active_count,
      COALESCE(SUM(status = 'inactive'),0) AS inactive_count,
      COALESCE(SUM(role = 'admin'),0) AS admin_count
      FROM user_accounts")->fetch_assoc();
    $accounts = (int)$stats['total'];
    $activeAccounts = (int)$stats['active_count'];
    $inactiveAccounts = (int)$stats['inactive_count'];
    $adminCount = (int)$stats['admin_count'];
    $studentCount = (int)$db->query('SELECT COUNT(*) AS n FROM students')->fetch_assoc()['n'];
    $eventCount = (int)$db->query('SELECT COUNT(*) AS n FROM events')->fetch_assoc()['n'];
    $registrationCount = (int)$db->query('SELECT COUNT(*) AS n FROM registrations')->fetch_assoc()['n'];
    $latest = $db->query('SELECT r.registration_id, r.registered_at,
       s.full_name, s.enrollment, e.title AS event_title
       FROM registrations r
       LEFT JOIN students s ON s.student_id = r.student_id
       LEFT JOIN events e ON e.event_id = r.event_id
       ORDER BY r.registered_at DESC, r.registration_id DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC);
    $auditRows = $db->query('SELECT a.audit_id, a.action, a.entity_type, a.entity_id,
       a.details, a.created_at, u.username AS actor
       FROM audit_logs a LEFT JOIN user_accounts u ON u.user_id = a.admin_user_id
       ORDER BY a.audit_id DESC LIMIT 12')->fetch_all(MYSQLI_ASSOC);
    $users = $db->query('SELECT user_id, full_name, username, role, status
       FROM user_accounts ORDER BY role ASC, user_id ASC LIMIT 50')->fetch_all(MYSQLI_ASSOC);
    $trend = $db->query("SELECT DATE_FORMAT(registered_at,'%Y-%m') AS month_key, COUNT(*) AS n
       FROM registrations
       WHERE registered_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
       GROUP BY DATE_FORMAT(registered_at,'%Y-%m') ORDER BY month_key ASC")->fetch_all(MYSQLI_ASSOC);
    $monthCounts = [];
    foreach ($trend as $item) { $monthCounts[(string)$item['month_key']] = (int)$item['n']; }
    $first = (new DateTimeImmutable('first day of this month'))->modify('-5 months');
    for ($i = 0; $i < 6; $i++) {
        $d = $first->modify('+' . $i . ' months');
        $monthly[] = ['label' => $d->format('M Y'), 'count' => $monthCounts[$d->format('Y-m')] ?? 0];
    }
    $db->close();
} catch (Throwable $e) {
    error_log('Practical 14 dashboard error: ' . $e->getMessage());
    http_response_code(503);
    $error = 'Dashboard database setup is incomplete. In phpMyAdmin select studenthub_db and import practical14.sql. Existing data will not be deleted.';
}
$maxMonth = 1;
foreach ($monthly as $item) $maxMonth = max($maxMonth, (int)$item['count']);
?>
<!doctype html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Practical 14 | StudentHub Admin Dashboard</title>
<link rel="stylesheet" href="css/practical14.css">
</head><body class="p14-body">
<header class="p14-header"><div class="p14-width">
  <a class="p14-brand" href="admin.php">StudentHub <span>/</span> Admin Dashboard</a>
  <?php p14_nav($admin, 'admin.php'); ?>
</div></header>
<main class="p14-width p14-main">
  <div class="p14-top"><div>
    <div class="p14-eyebrow">PRACTICAL 14 · PHP + MYSQL + ROLE-BASED ACCESS CONTROL</div>
    <h1>Administration overview</h1>
    <p>Welcome, <?=auth_e($admin['full_name'])?>. All numbers below are queried live from MySQL.</p>
  </div><div class="p14-top-buttons">
    <a class="p14-button p14-button-muted" href="student_directory.php">Manage students</a>
    <a class="p14-button p14-button-muted" href="event_management.php">Poster events</a>
    <a class="p14-button" href="ajax_events.php">AJAX events →</a>
  </div></div>
  <?php if ($error !== ''): ?>
    <div class="p14-alert p14-alert-error" role="alert"><?=auth_e($error)?></div>
  <?php else: ?>
    <?php if ($notice !== ''): ?><div class="p14-alert" role="status"><?=auth_e($notice)?></div><?php endif; ?>
    <section aria-label="Live database statistics" class="p14-stats">
      <div class="p14-stat"><span>Total user accounts</span><strong><?=auth_e($accounts)?></strong><small><?=auth_e($adminCount)?> administrators</small></div>
      <div class="p14-stat"><span>Student records</span><strong><?=auth_e($studentCount)?></strong><small>students table</small></div>
      <div class="p14-stat"><span>Events</span><strong><?=auth_e($eventCount)?></strong><small>events table</small></div>
      <div class="p14-stat"><span>Registrations</span><strong><?=auth_e($registrationCount)?></strong><small>registrations table</small></div>
      <div class="p14-stat p14-stat-accent"><span>Active / inactive users</span><strong><?=auth_e($activeAccounts)?> <em>/ <?=auth_e($inactiveAccounts)?></em></strong><small>role and status verified</small></div>
    </section>
    <div class="p14-columns">
      <section class="p14-card"><div class="p14-sectionhead"><div><span class="p14-eyebrow">LAST SIX CALENDAR MONTHS</span><h2>Event registrations by month</h2></div><span class="p14-pill">From MySQL</span></div>
        <p class="p14-subtitle">Counts use registration dates (not event dates).</p>
        <div class="p14-chart" role="img" aria-label="Monthly event registrations for the last six months">
        <?php foreach ($monthly as $m): ?>
          <div class="p14-bar-group"><span class="p14-count"><?=auth_e($m['count'])?></span>
            <div class="p14-bar-track"><div class="p14-bar" style="height:<?=max(0, min(100, (int)round(100 * $m['count'] / $maxMonth)))?>%"></div></div>
            <span class="p14-month"><?=auth_e($m['label'])?></span></div>
        <?php endforeach; ?></div>
      </section>
      <section class="p14-card"><div class="p14-sectionhead"><div><span class="p14-eyebrow">RECENT ACTIVITY</span><h2>Latest event registrations</h2></div><span class="p14-pill"><?=count($latest)?> shown</span></div>
        <div class="p14-tablewrap"><table><thead><tr><th>Student</th><th>Event</th><th>Registered</th></tr></thead><tbody>
        <?php if (!$latest): ?><tr><td colspan="3" class="p14-empty">No registrations yet.</td></tr><?php endif; ?>
        <?php foreach ($latest as $r): ?><tr>
          <td><strong><?=auth_e($r['full_name'] ?? 'Removed student')?></strong><small><?=auth_e($r['enrollment'] ?? '—')?></small></td>
          <td><?=auth_e($r['event_title'] ?? 'Removed event')?></td>
          <td class="p14-nowrap"><?=auth_e($r['registered_at'])?></td></tr><?php endforeach; ?>
        </tbody></table></div>
      </section>
    </div>
    <section class="p14-card p14-card-spaced"><div class="p14-sectionhead"><div><span class="p14-eyebrow">INTERMEDIATE EXTENSION</span><h2>User account management</h2></div><span class="p14-pill">CSRF protected</span></div>
      <p class="p14-subtitle">Toggle active/inactive access. Your own administrator account cannot be disabled. Changes are written to audit_logs.</p>
      <div class="p14-tablewrap"><table><thead><tr><th>Account</th><th>Role</th><th>Status</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($users as $person): $self = (int)$person['user_id'] === (int)$admin['user_id']; $isActive = $person['status'] === 'active'; ?>
          <tr><td><strong><?=auth_e($person['full_name'])?></strong><small><?=auth_e($person['username'])?></small></td>
          <td><span class="p14-role"><?=auth_e(ucfirst($person['role']))?></span></td>
          <td><span class="p14-status <?=$isActive ? 'p14-active' : 'p14-inactive'?>"><?=auth_e(ucfirst($person['status']))?></span></td>
          <td>
            <?php if ($self): ?><span class="p14-self">Your account</span>
            <?php else: ?><form class="p14-inline-form" action="account_status.php" method="post" onsubmit="return confirm('Change this account status?');">
              <input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>">
              <input type="hidden" name="user_id" value="<?=auth_e($person['user_id'])?>">
              <input type="hidden" name="new_status" value="<?=$isActive ? 'inactive' : 'active'?>">
              <button type="submit" class="p14-mini <?=$isActive ? 'p14-mini-red' : 'p14-mini-green'?>"><?=$isActive ? 'Deactivate' : 'Activate'?></button>
            </form><?php endif; ?>
          </td></tr>
        <?php endforeach; ?>
      </tbody></table></div><p class="p14-footnote">Showing up to 50 accounts; changes immediately update the database on submission.</p>
    </section>
    <section class="p14-card p14-card-spaced"><div class="p14-sectionhead"><div><span class="p14-eyebrow">AUDIT TRAIL</span><h2>Recent administrative actions</h2></div><span class="p14-pill">audit_logs</span></div>
      <div class="p14-tablewrap"><table><thead><tr><th>Time</th><th>Administrator</th><th>Action</th><th>Target</th><th>Details</th></tr></thead><tbody>
      <?php if (!$auditRows): ?><tr><td colspan="5" class="p14-empty">No admin changes recorded yet. Add/edit/delete a test event or toggle a test account.</td></tr><?php endif; ?>
      <?php foreach ($auditRows as $a): ?><tr><td class="p14-nowrap"><?=auth_e($a['created_at'])?></td>
      <td><?=auth_e($a['actor'] ?? 'Unknown')?></td><td><span class="p14-action"><?=auth_e($a['action'])?></span></td>
      <td><?=auth_e($a['entity_type'])?> #<?=auth_e($a['entity_id'] ?? '—')?></td><td><?=auth_e($a['details'])?></td></tr><?php endforeach; ?>
      </tbody></table></div>
    </section>
  <?php endif; ?>
  <form class="p14-signout" action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>"><button type="submit">Log out securely</button></form>
</main><footer class="p14-footer">© 2026 StudentHub · Practical 14 · Admin-only Dashboard</footer>
</body></html>
