<?php
/** POST /api/events_delete.php -- JSON {"event_id":123}; prevents accidental related-data loss. */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/p14_audit.php';
p13_method('POST');
$db = p13_admin();
$body = p13_read_json();
$id = p13_id($body['event_id'] ?? null);
try {
    $db->begin_transaction();
    $stmt = $db->prepare('SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $linked = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    if ($linked > 0) { $db->rollback(); $db->close(); p13_fail(409, 'Event has registrations. To preserve students, delete a new test event instead.', 'EVENT_IN_USE'); }
    $stmt = $db->prepare('DELETE FROM events WHERE event_id=? AND NOT EXISTS (SELECT 1 FROM registrations WHERE event_id=?)');
    $stmt->bind_param('ii',$id,$id);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();
    if ($deleted !== 1) { $db->rollback(); $db->close(); p13_fail(404, 'Event not found or already removed.', 'NOT_FOUND'); }
    p14_audit($db, (int)$_SESSION['auth_uid'], 'event_deleted', 'event', (int)$id, 'Event ID: ' . $id);
    $db->commit(); $db->close();
    p13_json(200, ['success'=>true,'message'=>'Event deleted without a page reload.', 'data'=>['event_id'=>$id]]);
} catch (Throwable $e) {
    try { $db->rollback(); } catch (Throwable $ignored) {}
    p13_db_error($e);
}
