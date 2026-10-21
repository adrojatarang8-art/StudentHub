<?php
/** POST /api/events_create.php -- JSON body; returns HTTP 201. */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/p14_audit.php';
p13_method('POST');
$db = p13_admin();
$body = p13_read_json();
$f = p13_fields($body);
try {
    $db->begin_transaction();
    $stmt = $db->prepare('INSERT INTO events (title,event_date,venue,category,description) VALUES (?,?,?,?,?)');
    $stmt->bind_param('sssss', $f['title'],$f['event_date'],$f['venue'],$f['category'],$f['description']);
    $stmt->execute();
    $id = $db->insert_id;
    $stmt->close();
    p14_audit($db, (int)$_SESSION['auth_uid'], 'event_created', 'event', (int)$id, 'Title: ' . $f['title']);
    $db->commit();
    $db->close();
    p13_json(201, ['success'=>true,'message'=>'Event added without reloading the page.', 'data'=>['event_id'=>$id]]);
} catch (Throwable $e) {
    try { $db->rollback(); } catch (Throwable $ignored) {}
    p13_db_error($e);
}
