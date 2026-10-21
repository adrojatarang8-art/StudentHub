<?php
/** POST /api/events_create.php -- JSON body; returns HTTP 201. */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
p13_method('POST');
$db = p13_admin();
$body = p13_read_json();
$f = p13_fields($body);
try {
    $stmt = $db->prepare('INSERT INTO events (title,event_date,venue,category,description) VALUES (?,?,?,?,?)');
    $stmt->bind_param('sssss', $f['title'],$f['event_date'],$f['venue'],$f['category'],$f['description']);
    $stmt->execute();
    $id = $db->insert_id;
    $stmt->close();
    $db->close();
    p13_json(201, ['success'=>true,'message'=>'Event added without reloading the page.', 'data'=>['event_id'=>$id]]);
} catch (Throwable $e) { p13_db_error($e); }
