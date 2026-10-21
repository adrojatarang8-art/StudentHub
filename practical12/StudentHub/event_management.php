<?php
/** Practical 12 — secure event CRUD with validated poster uploads (no JS required for saving). */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/p12_events_helpers.php';
$admin = auth_require_role('admin');
$adminId = (int)$admin['user_id'];
$errors = [];
$dbError = '';
$noticeMap = [
    'created' => 'Event added successfully. The database record has been saved.',
    'updated' => 'Event updated successfully.',
    'deleted' => 'Event deleted successfully.',
];
$notice = $noticeMap[(string)($_GET['notice'] ?? '')] ?? '';
$defaults = ['title'=>'','event_date'=>'','venue'=>'','category'=>'Technical','description'=>'','event_status'=>'open'];
$form = $defaults;
$editingId = 0;
$editingPoster = null;
$rows = [];
$stats = ['total'=>0,'open_events'=>0,'closed_events'=>0,'posters'=>0];
$categories = P12_CATEGORIES;
$query = trim((string)($_GET['q'] ?? ''));
$categoryFilter = (string)($_GET['category'] ?? 'all');
$statusFilter = (string)($_GET['status'] ?? 'all');
if (!in_array($categoryFilter, array_merge(['all'], $categories), true)) $categoryFilter = 'all';
if (!in_array($statusFilter, ['all','open','closed'], true)) $statusFilter = 'all';
if (strlen($query) > 100) $query = substr($query, 0, 100);
$totalMatches = 0;
$page = 1;
$totalPages = 1;
$logs = [];

try {
    $db = getMySQLi();
    $db->query('SELECT event_id, title, event_date, venue, category, description, event_status, poster_path FROM events LIMIT 0');
    $db->query('SELECT audit_id FROM event_audit_log LIMIT 0');
} catch (Throwable $ex) {
    error_log('Practical12 setup failed: ' . $ex->getMessage());
    $dbError = 'Database is not ready. Import practical12.sql into studenthub_db in phpMyAdmin and check MySQL is running.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $editingId = filter_var($_POST['event_id'] ?? 0, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 0;
    if ($dbError !== '') {
        $errors[] = $dbError;
    } elseif (!auth_check_csrf((string)($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Security check failed. Refresh the page and try again.';
    } else {
        $newPoster = null;
        $oldPosterForCleanup = null;
        $tx = false;
        try {
            if ($action === 'create' || $action === 'update') {
                if ($action === 'update' && $editingId < 1) throw new DomainException('Invalid event ID.');
                foreach ($defaults as $field => $default) $form[$field] = trim((string)($_POST[$field] ?? ''));
                [$title,$date,$venue,$category,$description,$status] = p12_validate_fields();
                $upload = p12_validate_poster();
                // Validate inputs and uploaded bytes first, then start transaction.
                // File is deleted on rollback if the DB update fails.
                if ($upload !== null) $newPoster = p12_move_poster($upload);
                $db->begin_transaction();
                $tx = true;
                if ($action === 'create') {
                    p12_db_write($db, 'INSERT INTO events(title,event_date,venue,category,description,event_status,poster_path) VALUES (?,?,?,?,?,?,?)',
                        'sssssss', [$title,$date,$venue,$category,$description,$status,$newPoster]);
                    $createdId = (int)$db->insert_id;
                    p12_log($db, $adminId, $createdId, 'created', $title);
                } else {
                    $old = p12_db_query($db, 'SELECT poster_path FROM events WHERE event_id = ? FOR UPDATE', 'i', [$editingId])->fetch_assoc();
                    if (!$old) throw new DomainException('This event no longer exists.');
                    $oldPath = $old['poster_path'] === null ? null : (string)$old['poster_path'];
                    $removeRequested = ($_POST['remove_poster'] ?? '') === 'yes';
                    $finalPoster = $newPoster ?? ($removeRequested ? null : $oldPath);
                    p12_db_write($db, 'UPDATE events SET title=?,event_date=?,venue=?,category=?,description=?,event_status=?,poster_path=? WHERE event_id=?',
                        'sssssssi', [$title,$date,$venue,$category,$description,$status,$finalPoster,$editingId]);
                    p12_log($db, $adminId, $editingId, 'updated', $title . ($newPoster ? ' (poster replaced)' : ($removeRequested ? ' (poster removed)' : '')));
                    if ($newPoster !== null || $removeRequested) $oldPosterForCleanup = $oldPath;
                }
                $db->commit();
                $tx = false;
                if ($oldPosterForCleanup !== null) p12_remove_poster($oldPosterForCleanup);
                p12_go($action === 'create' ? 'created' : 'updated');
            } elseif ($action === 'delete') {
                if ($editingId < 1 || ($_POST['confirm_delete'] ?? '') !== 'yes') throw new DomainException('Deletion must be confirmed.');
                $db->begin_transaction();
                $tx = true;
                $event = p12_db_query($db, 'SELECT title,poster_path FROM events WHERE event_id = ? FOR UPDATE', 'i', [$editingId])->fetch_assoc();
                if (!$event) throw new DomainException('Event not found.');
                $joined = p12_db_query($db, 'SELECT COUNT(*) AS n FROM registrations WHERE event_id = ?', 'i', [$editingId])->fetch_assoc();
                if ((int)($joined['n'] ?? 0) !== 0) {
                    throw new DomainException('This event has student registrations. Deleting it would remove linked records, so deletion is blocked. Try a newly created test event instead.');
                }
                p12_db_write($db, 'DELETE FROM events WHERE event_id = ?', 'i', [$editingId]);
                p12_log($db, $adminId, $editingId, 'deleted', (string)$event['title']);
                $oldPosterForCleanup = $event['poster_path'] === null ? null : (string)$event['poster_path'];
                $db->commit();
                $tx = false;
                p12_remove_poster($oldPosterForCleanup);
                p12_go('deleted');
            } else {
                throw new DomainException('Invalid form action.');
            }
        } catch (DomainException $ex) {
            if ($tx) $db->rollback();
            if ($newPoster !== null) p12_remove_poster($newPoster);
            $errors[] = $ex->getMessage();
        } catch (Throwable $ex) {
            if ($tx) $db->rollback();
            if ($newPoster !== null) p12_remove_poster($newPoster);
            error_log('Practical12 operation error: ' . $ex->getMessage());
            $errors[] = 'Could not complete the operation. Check your data, SQL setup and upload folder permissions.';
        }
        if ($action === 'create' || $action === 'delete') $editingId = 0;
    }
}

if ($dbError === '' && isset($db)) {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['edit'])) {
            $editingId = filter_var($_GET['edit'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 0;
            if ($editingId > 0) {
                $edited = p12_db_query($db, 'SELECT title,event_date,venue,category,description,event_status,poster_path FROM events WHERE event_id=?', 'i', [$editingId])->fetch_assoc();
                if ($edited) { $form = array_replace($form, $edited); $editingPoster = p12_safe_poster_path($edited['poster_path']); }
                else { $editingId = 0; $errors[] = 'Event was not found.'; }
            }
        }
        if ($editingId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $edited = p12_db_query($db, 'SELECT poster_path FROM events WHERE event_id=?', 'i', [$editingId])->fetch_assoc();
            $editingPoster = $edited ? p12_safe_poster_path($edited['poster_path']) : null;
        }
        $stats = $db->query("SELECT COUNT(*) total, COALESCE(SUM(event_status='open'),0) open_events, COALESCE(SUM(event_status='closed'),0) closed_events, COALESCE(SUM(poster_path IS NOT NULL AND poster_path<>''),0) posters FROM events")->fetch_assoc() ?: $stats;
        $where = [];
        $args = [];
        $types = '';
        if ($query !== '') {
            $where[] = '(title LIKE ? OR venue LIKE ? OR description LIKE ?)';
            $term = '%' . addcslashes($query, '%_\\') . '%';
            array_push($args, $term, $term, $term);
            $types .= 'sss';
        }
        if ($categoryFilter !== 'all') { $where[] = 'category = ?'; $args[] = $categoryFilter; $types .= 's'; }
        if ($statusFilter !== 'all') { $where[] = 'event_status = ?'; $args[] = $statusFilter; $types .= 's'; }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $matched = p12_db_query($db, 'SELECT COUNT(*) n FROM events' . $whereSql, $types, $args)->fetch_assoc();
        $totalMatches = (int)($matched['n'] ?? 0);
        $totalPages = max(1, (int)ceil($totalMatches / 10));
        $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 1;
        $page = min($requestedPage, $totalPages);
        $args2 = array_merge($args, [10, ($page - 1) * 10]);
        $rows = p12_db_query($db,
            'SELECT e.event_id,e.title,e.event_date,e.venue,e.category,e.description,e.event_status,e.poster_path, (SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.event_id) AS registrations_count FROM events e'
            . $whereSql . ' ORDER BY e.event_id DESC LIMIT ? OFFSET ?', $types . 'ii', $args2)->fetch_all(MYSQLI_ASSOC);
        $logs = $db->query('SELECT a.event_id,a.action,a.details,a.created_at, COALESCE(u.username, CONCAT("Admin #",a.actor_user_id)) AS admin_name FROM event_audit_log a LEFT JOIN user_accounts u ON u.user_id=a.actor_user_id ORDER BY a.audit_id DESC LIMIT 6')->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $ex) {
        error_log('Practical12 listing error: ' . $ex->getMessage());
        $dbError = 'Could not load events. Check practical12.sql and MySQL connection.';
    }
    $db->close();
}
function p12_filter_url(array $override = []): string {
    global $query, $categoryFilter, $statusFilter;
    $params = array_merge(['q'=>$query,'category'=>$categoryFilter,'status'=>$statusFilter], $override);
    return 'event_management.php?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Practical 12 | Event Management - StudentHub</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/practical12.css">
</head>
<body class="p10-body p11-body p12-body">
<header class="p10-header">
  <h1>StudentHub <span class="p12-slash">/</span> Event Management</h1>
  <nav aria-label="Main navigation"><a href="index.html">Home</a><a href="admin.php">Admin Dashboard</a><a href="student_directory.php">Manage Students</a><a href="event_management.php" aria-current="page">Manage Events</a><span class="p12-admin">Signed in: <?=auth_e($admin['username'])?> (Admin)</span></nav>
</header>
<main class="p11-shell p12-shell">
  <section class="p11-intro p12-intro">
    <div><div class="p10-eyebrow">PRACTICAL 12 · PHP &amp; MySQLi · SECURE FILE UPLOAD</div><h2>Event Management</h2><p>Create events, upload validated posters and manage categories, registrations and event status.</p></div>
    <div class="p11-top-actions"><a class="p11-outline-btn" href="event_management.php">+ New Event</a><a class="p12-guide" href="README-Practical12.txt" target="_blank" rel="noopener">Setup Guide</a></div>
  </section>
  <?php if ($notice): ?><div class="p10-notice" role="status"><?=auth_e($notice)?></div><?php endif; ?>
  <?php foreach ($errors as $error): ?><div class="p10-error" role="alert"><?=auth_e($error)?></div><?php endforeach; ?>
  <?php if ($dbError !== ''): ?><div class="p10-error" role="alert"><?=auth_e($dbError)?></div><?php else: ?>
  <div class="p11-stats" aria-label="Event statistics">
    <div class="p11-stat"><span>Total Events</span><strong><?=auth_e($stats['total'])?></strong></div>
    <div class="p11-stat"><span>Open Events</span><strong><?=auth_e($stats['open_events'])?></strong></div>
    <div class="p11-stat"><span>Closed Events</span><strong><?=auth_e($stats['closed_events'])?></strong></div>
    <div class="p11-stat"><span>With Posters</span><strong><?=auth_e($stats['posters'])?></strong></div>
  </div>
  <div class="p12-grid">
    <section class="p11-panel p12-form-card" aria-labelledby="p12-form-title">
      <div class="p12-kicker"><?= $editingId > 0 ? 'EDIT EXISTING RECORD' : 'CREATE NEW EVENT' ?></div>
      <h3 id="p12-form-title"><?= $editingId > 0 ? 'Edit Event #' . (int)$editingId : 'Add New Event' ?></h3>
      <p>Only administrators can save changes. Poster files are checked on the server.</p>
      <form method="post" enctype="multipart/form-data" class="p12-event-form" id="p12-event-form">
        <input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>">
        <input type="hidden" name="action" value="<?= $editingId > 0 ? 'update' : 'create' ?>">
        <input type="hidden" name="event_id" value="<?= (int)$editingId ?>">
        <label for="title">Event Title <b>*</b></label>
        <input id="title" name="title" required maxlength="150" placeholder="Tech Fest 2026" value="<?=auth_e($form['title'])?>">
        <div class="p12-form-row"><div><label for="event_date">Event Date <b>*</b></label><input id="event_date" type="date" name="event_date" required value="<?=auth_e($form['event_date'])?>"></div><div><label for="category">Category <b>*</b></label><select id="category" name="category" required><?php foreach ($categories as $category): ?><option value="<?=auth_e($category)?>" <?= $form['category'] === $category ? 'selected' : '' ?>><?=auth_e($category)?></option><?php endforeach; ?></select></div></div>
        <label for="venue">Venue <b>*</b></label><input id="venue" name="venue" required maxlength="150" placeholder="College Auditorium" value="<?=auth_e($form['venue'])?>">
        <label for="event_status">Event Status <b>*</b></label><select id="event_status" name="event_status"><option value="open" <?= $form['event_status'] === 'open' ? 'selected' : '' ?>>Open</option><option value="closed" <?= $form['event_status'] === 'closed' ? 'selected' : '' ?>>Closed</option></select>
        <label for="description">Description <b>*</b></label><textarea id="description" name="description" rows="3" maxlength="2000" required placeholder="Event details..."><?=auth_e($form['description'])?></textarea>
        <label for="poster">Event Poster (optional)</label><input id="poster" type="file" name="poster" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"><small class="p12-help">JPG, PNG or WEBP only · Maximum 2 MB · Server-side validation</small>
        <?php if ($editingPoster): ?><div class="p12-poster-current"><img src="<?=auth_e($editingPoster)?>" alt="Current poster" loading="lazy"><label class="p12-checkbox"><input type="checkbox" name="remove_poster" value="yes"> Remove existing poster</label></div><?php endif; ?>
        <div id="p12-preview" class="p12-preview" hidden><span>Selected poster preview</span><img id="p12-preview-img" alt="Selected poster preview"></div>
        <button type="submit" class="p12-primary"><?= $editingId > 0 ? '✓ Update Event' : '+ Add Event' ?></button>
        <?php if ($editingId > 0): ?><a class="p12-cancel" href="event_management.php">Cancel Edit</a><?php endif; ?>
      </form>
    </section>
    <section class="p11-panel p12-list-card" aria-labelledby="p12-events-title">
      <div class="p12-section-heading"><div><div class="p12-kicker">DATABASE RECORDS</div><h3 id="p12-events-title">All Events</h3></div><span class="p12-counter"><?= (int)$totalMatches ?> result(s)</span></div>
      <form class="p12-filters" method="get" action="event_management.php">
        <div><label for="q">Search by title, venue or description</label><input type="search" id="q" name="q" placeholder="Search events..." value="<?=auth_e($query)?>"></div>
        <div><label for="filter-cat">Category</label><select id="filter-cat" name="category"><option value="all">All Categories</option><?php foreach ($categories as $category): ?><option value="<?=auth_e($category)?>" <?= $categoryFilter === $category ? 'selected' : '' ?>><?=auth_e($category)?></option><?php endforeach; ?></select></div>
        <div><label for="filter-status">Status</label><select id="filter-status" name="status"><option value="all">All Statuses</option><option value="open" <?= $statusFilter === 'open' ? 'selected' : '' ?>>Open</option><option value="closed" <?= $statusFilter === 'closed' ? 'selected' : '' ?>>Closed</option></select></div>
        <div class="p12-filter-buttons"><button type="submit">Apply</button><a href="event_management.php">Reset</a></div>
      </form>
      <div class="p11-tablewrap"><table class="p11-table p12-table"><thead><tr><th>Poster / ID</th><th>Event</th><th>Category</th><th>Date / Venue</th><th>Status</th><th>Actions</th></tr></thead><tbody>
      <?php if (!$rows): ?><tr><td colspan="6" class="p11-empty">No events match these filters.</td></tr><?php endif; ?>
      <?php foreach ($rows as $item): ?>
      <tr>
        <td><div class="p12-small-poster"><?php $img = p12_safe_poster_path($item['poster_path']); if ($img): ?><img src="<?=auth_e($img)?>" alt="Poster for <?=auth_e($item['title'])?>" loading="lazy"><?php else: ?><span aria-hidden="true">🖼️</span><?php endif; ?></div><small>#<?= (int)$item['event_id'] ?></small></td>
        <td><strong><?=auth_e($item['title'])?></strong><small><?= (int)$item['registrations_count'] ?> student registration(s)</small></td>
        <td><?=auth_e($item['category'])?></td>
        <td><?=auth_e($item['event_date'])?><small><?=auth_e($item['venue'])?></small></td>
        <td><span class="p11-pill <?= $item['event_status'] === 'open' ? 'p11-active' : 'p12-closed' ?>"><?=auth_e(ucfirst((string)$item['event_status']))?></span></td>
        <td><div class="p11-row-actions"><a href="<?=auth_e(p12_filter_url(['edit'=>(int)$item['event_id']]))?>">Edit</a>
          <form method="post" class="p12-delete-form" data-event="<?=auth_e($item['title'])?>"><input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="event_id" value="<?= (int)$item['event_id'] ?>"><input type="hidden" name="confirm_delete" value="yes"><button class="p11-delete" type="submit" <?= (int)$item['registrations_count'] > 0 ? 'disabled title="Cannot delete event with student registrations"' : '' ?>>Delete</button></form></div></td>
      </tr><?php endforeach; ?>
      </tbody></table></div>
      <div class="p11-list-footer"><span>Page <?= (int)$page ?> of <?= (int)$totalPages ?> · 10 events per page</span><div class="p11-pages"><?php for ($i=1;$i<=$totalPages;$i++): ?><a class="<?= $page === $i ? 'selected' : '' ?>" href="<?=auth_e(p12_filter_url(['page'=>$i]))?>"><?= $i ?></a><?php endfor; ?></div></div>
      <p class="p12-info">Events with registered students are protected from permanent deletion. Edit or close them instead.</p>
    </section>
  </div>
  <section class="p11-panel p12-audit" aria-labelledby="p12-audit-title"><div class="p12-kicker">ADVANCED EXTENSION</div><h3 id="p12-audit-title">Recent Event Audit Log</h3><div class="p11-tablewrap"><table class="p11-table"><thead><tr><th>Action</th><th>Event ID</th><th>Admin</th><th>Time</th><th>Details</th></tr></thead><tbody><?php if (!$logs): ?><tr><td colspan="5" class="p11-empty">No changes recorded yet.</td></tr><?php endif; ?><?php foreach ($logs as $log): ?><tr><td><?=auth_e($log['action'])?></td><td>#<?= (int)$log['event_id'] ?></td><td><?=auth_e($log['admin_name'])?></td><td><?=auth_e($log['created_at'])?></td><td><?=auth_e($log['details'])?></td></tr><?php endforeach; ?></tbody></table></div></section>
  <?php endif; ?>
  <p class="p11-footer-note">Practical 12 · MySQLi Prepared Statements · Validated Poster Upload · Admin-only CRUD</p>
</main>
<script src="js/practical12.js" defer></script>
</body>
</html>
