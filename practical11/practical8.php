<?php
session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['p8_csrf'])) $_SESSION['p8_csrf'] = bin2hex(random_bytes(32));
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$error = ''; $notice = ''; $students = []; $events = []; $registrations = [];
try {
    $pdo = getPDO();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['p8_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
            $error = 'Invalid form token. Refresh this page and try again.';
        } else {
            $studentId = filter_var($_POST['student_id'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
            $eventId = filter_var($_POST['event_id'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
            if (!$studentId || !$eventId) {
                $error = 'Please choose a valid student and event.';
            } else {
                // Prepared statements: values are sent separately from SQL.
                $studentStmt = $pdo->prepare('SELECT student_id FROM students WHERE student_id = :id');
                $studentStmt->execute(['id'=>$studentId]);
                $eventStmt = $pdo->prepare('SELECT event_id FROM events WHERE event_id = :id');
                $eventStmt->execute(['id'=>$eventId]);
                if (!$studentStmt->fetch() || !$eventStmt->fetch()) {
                    $error = 'Student or event does not exist.';
                } else {
                    $insert = $pdo->prepare('INSERT INTO registrations (student_id, event_id) VALUES (:student_id, :event_id)');
                    try {
                        $insert->execute(['student_id'=>$studentId, 'event_id'=>$eventId]);
                        $_SESSION['p8_flash'] = 'Event registration saved successfully in MySQL!';
                        $_SESSION['p8_csrf'] = bin2hex(random_bytes(32));
                        header('Location: practical8.php'); exit;
                    } catch (PDOException $e) {
                        if ($e->getCode() === '23000') $error = 'This student is already registered for this event.';
                        else throw $e;
                    }
                }
            }
        }
    }
    if (!empty($_SESSION['p8_flash'])) { $notice = $_SESSION['p8_flash']; unset($_SESSION['p8_flash']); }
    $students = $pdo->query('SELECT student_id, full_name, enrollment FROM students ORDER BY full_name')->fetchAll();
    $events = $pdo->query('SELECT event_id, title, event_date FROM events ORDER BY event_date DESC')->fetchAll();
    $registrations = $pdo->query('SELECT r.registration_id, s.full_name, s.enrollment, e.title, r.registered_at FROM registrations AS r INNER JOIN students AS s ON r.student_id=s.student_id INNER JOIN events AS e ON r.event_id=e.event_id ORDER BY r.registration_id DESC LIMIT 100')->fetchAll();
} catch (PDOException $e) {
    error_log('StudentHub P8 DB error: '.$e->getMessage());
    $error = 'Database unavailable. Start MySQL and import studenthub_db.sql before opening this page.';
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>StudentHub | Event Registration</title><link rel="stylesheet" href="css/style.css"></head>
<body><header><h1>StudentHub Portal</h1><nav><a href="index.html">Home</a><a href="register.php">Student Registration</a><a href="practical8.php">Event Registration</a><a href="db_test.php">Database Status</a><a href="secure_account.php">Create Account</a></nav></header>
<main><section class="p8-panel"><h2>MySQL Event Registrations</h2><p>PDO + prepared statements | students ↔ registrations ↔ events</p>
<?php if ($error): ?><div class="p8-error"><?= h($error) ?></div><?php endif; ?>
<?php if ($notice): ?><div class="p8-success"><?= h($notice) ?></div><?php endif; ?>
<?php if ($students && $events): ?><form method="POST" action="practical8.php"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['p8_csrf']) ?>">
<label for="student_id">Select student</label><select id="student_id" name="student_id" required><option value="">-- Choose Student --</option><?php foreach ($students as $s): ?><option value="<?= (int)$s['student_id'] ?>"><?= h($s['full_name'].' ('.$s['enrollment'].')') ?></option><?php endforeach; ?></select>
<label for="event_id">Select event</label><select id="event_id" name="event_id" required><option value="">-- Choose Event --</option><?php foreach ($events as $e): ?><option value="<?= (int)$e['event_id'] ?>"><?= h($e['title'].' — '.$e['event_date']) ?></option><?php endforeach; ?></select>
<button type="submit">Save Registration in MySQL</button></form><?php endif; ?>
<h3>Database Registrations</h3><div class="p8-table-wrap"><table class="p8-table"><thead><tr><th>ID</th><th>Student</th><th>Enrollment</th><th>Event</th><th>Registered At</th></tr></thead><tbody>
<?php foreach ($registrations as $r): ?><tr><td><?= (int)$r['registration_id'] ?></td><td><?= h($r['full_name']) ?></td><td><?= h($r['enrollment']) ?></td><td><?= h($r['title']) ?></td><td><?= h($r['registered_at']) ?></td></tr><?php endforeach; ?>
<?php if (!$registrations): ?><tr><td colspan="5">No MySQL event registrations yet.</td></tr><?php endif; ?></tbody></table></div>
</section></main></body></html>
