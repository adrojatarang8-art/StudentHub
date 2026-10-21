<?php
/** Practical 14: admin-only status change, POST + CSRF + audit logging. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/p14_audit.php';
$actor = auth_require_role('admin');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST'); http_response_code(405); exit('Method not allowed.');
}
if (!auth_check_csrf((string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(403); exit('Invalid security token. Refresh the dashboard.');
}
$id = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$status = (string)($_POST['new_status'] ?? '');
if ($id === false || $id === null || !in_array($status, ['active', 'inactive'], true)) {
    http_response_code(422); exit('Invalid account or status.');
}
if ((int)$actor['user_id'] === (int)$id && $status === 'inactive') {
    http_response_code(403); exit('You cannot deactivate your own administrator account.');
}
$db = null;
try {
    $db = getMySQLi();
    $db->begin_transaction();
    $stmt = $db->prepare('SELECT user_id, username, role, status FROM user_accounts WHERE user_id = ? FOR UPDATE');
    $stmt->bind_param('i', $id);
    $stmt->execute(); $target = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$target) throw new DomainException('Account not found.');
    if ($target['status'] === $status) {
        $db->commit(); $db->close();
        header('Location: admin.php?notice=unchanged', true, 303); exit;
    }
    if ($target['role'] === 'admin' && $status === 'inactive') {
        // A second admin can be deactivated only while another ACTIVE admin exists.
        $numAdmins = (int)$db->query("SELECT COUNT(*) AS n FROM user_accounts WHERE role='admin' AND status='active'")->fetch_assoc()['n'];
        if ($numAdmins < 2) throw new DomainException('At least one active administrator must remain.');
    }
    $stmt = $db->prepare('UPDATE user_accounts SET status = ? WHERE user_id = ?');
    $stmt->bind_param('si', $status, $id); $stmt->execute(); $stmt->close();
    if ($status === 'inactive') {
        $stmt = $db->prepare('DELETE FROM remember_tokens WHERE user_id = ?');
        $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    }
    $action = $status === 'active' ? 'account_activated' : 'account_deactivated';
    p14_audit($db, (int)$actor['user_id'], $action, 'user_account', (int)$id, 'Username: ' . $target['username']);
    $db->commit(); $db->close();
    header('Location: admin.php?notice=' . $status, true, 303); exit;
} catch (DomainException $e) {
    if ($db instanceof mysqli) $db->rollback();
    http_response_code(409); exit(auth_e($e->getMessage()));
} catch (Throwable $e) {
    if ($db instanceof mysqli) $db->rollback();
    error_log('P14 account status update: ' . $e->getMessage());
    http_response_code(503); exit('Account could not be updated. Import practical14.sql and try again.');
}
