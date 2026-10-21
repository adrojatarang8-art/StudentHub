<?php
/** GET /api/events_list.php?q=PHP&category=Workshop */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
p13_method('GET');
$db = p13_admin();
$q = trim((string)($_GET['q'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
if ((function_exists('mb_strlen') ? mb_strlen($q, 'UTF-8') : strlen($q)) > 100 ||
    (function_exists('mb_strlen') ? mb_strlen($category, 'UTF-8') : strlen($category)) > 50) {
    p13_fail(422, 'Search or category filter is too long.', 'INVALID_FILTER');
}
try {
    $sql = 'SELECT event_id,title,event_date,venue,category,description FROM events';
    $where = [];
    $args = [];
    $types = '';
    if ($q !== '') {
        $where[] = '(title LIKE ? OR venue LIKE ? OR category LIKE ? OR description LIKE ?)';
        $pattern = '%' . $q . '%';
        array_push($args, $pattern, $pattern, $pattern, $pattern);
        $types .= 'ssss';
    }
    if ($category !== '') {
        $where[] = 'category = ?';
        $args[] = $category;
        $types .= 's';
    }
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY event_date DESC, event_id DESC LIMIT 250';
    $stmt = $db->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$args);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $totalRow = $db->query('SELECT COUNT(*) AS total FROM events')->fetch_assoc();
    $db->close();
    p13_json(200, ['success'=>true,'message'=>'Events loaded successfully.', 'data'=>$events,
        'meta'=>['shown'=>count($events), 'total'=>(int)$totalRow['total'], 'limit'=>250]]);
} catch (Throwable $e) {
    p13_db_error($e);
}
