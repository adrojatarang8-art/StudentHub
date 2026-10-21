<?php
/** POST /api/events_update.php -- JSON body including event_id. */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
p13_method('POST');
$db = p13_admin();
$body = p13_read_json();
$id = p13_id($body['event_id'] ?? null);
$f = p13_fields($body);
try {
    $exists = $db->prepare('SELECT event_id FROM events WHERE event_id = ? LIMIT 1');
    $exists->bind_param('i', $id);
    $exists->execute();
    $found = (bool)$exists->get_result()->fetch_assoc();
    $exists->close();
    if (!$found) p13_fail(404, 'Event not found.', 'NOT_FOUND');
    $stmt = $db->prepare('UPDATE events SET title=?,event_date=?,venue=?,category=?,description=? WHERE event_id=?');
    $stmt->bind_param('sssssi', $f['title'],$f['event_date'],$f['venue'],$f['category'],$f['description'],$id);
    $stmt->execute();
    $stmt->close();
    $db->close();
    p13_json(200, ['success'=>true,'message'=>'Event saved without a page reload.', 'data'=>['event_id'=>$id]]);
} catch (Throwable $e) { p13_db_error($e); }
