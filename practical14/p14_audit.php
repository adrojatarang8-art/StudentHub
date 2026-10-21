<?php
/** Write-only helper for important admin mutations; call inside the same transaction. */
declare(strict_types=1);
function p14_audit(mysqli $db, int $actorId, string $action, string $entityType, ?int $entityId, string $details): void {
    $details = substr($details, 0, 255);
    $stmt = $db->prepare('INSERT INTO audit_logs (admin_user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('issis', $actorId, $action, $entityType, $entityId, $details);
    $stmt->execute();
    $stmt->close();
}
