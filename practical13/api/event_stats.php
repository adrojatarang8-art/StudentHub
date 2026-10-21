<?php
/** GET /api/event_stats.php - counts used by the dashboard. */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
p13_method('GET');
$db = p13_admin();
try {
    $result = $db->query("SELECT COUNT(*) AS total, COALESCE(SUM(event_date >= CURDATE()),0) AS upcoming FROM events")->fetch_assoc();
    $db->close();
    p13_json(200, ['success'=>true,'data'=>['total'=>(int)$result['total'],'upcoming'=>(int)$result['upcoming']]]);
} catch (Throwable $e) { p13_db_error($e); }
