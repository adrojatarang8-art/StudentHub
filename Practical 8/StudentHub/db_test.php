<?php
require_once __DIR__ . '/db.php';
$connected = false;
try {
    $pdo = getPDO();
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $studentCount = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $eventCount = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $registrationCount = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    $connected = true;
} catch (PDOException $e) {
    error_log('StudentHub DB connection error: ' . $e->getMessage());
    $message = 'Connection failed. Check MySQL is running, SQL was imported, and credentials in db.php are correct.';
}
function escapeHtml($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Practical 8 | Database Test</title><link rel="stylesheet" href="css/style.css"></head><body>
<header><h1>StudentHub</h1><nav><a href="index.html">Home</a> <a href="practical8.php">MySQL Registrations</a> <a href="db_test.php">Connection Test</a></nav></header>
<main><section class="p8-panel"><h2>MySQL PDO Connection Test</h2>
<?php if ($connected): ?>
<div class="p8-success">✓ Database connected successfully using PHP PDO!</div>
<p><strong>Database:</strong> studenthub_db</p>
<p><strong>Tables:</strong> <?= escapeHtml(implode(', ', $tables)) ?></p>
<table class="p8-table"><tr><th>Students</th><th>Events</th><th>Registrations</th></tr><tr><td><?= $studentCount ?></td><td><?= $eventCount ?></td><td><?= $registrationCount ?></td></tr></table>
<p>Prepared statement example is available in <a href="practical8.php">Practical 8 Registrations</a>.</p>
<?php else: ?><div class="p8-error"><?= escapeHtml($message) ?></div><?php endif; ?>
</section></main></body></html>
