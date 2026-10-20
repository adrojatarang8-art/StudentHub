<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
auth_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed. Use the logout button in your dashboard.');
}
if (!auth_check_csrf((string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    exit('Invalid logout request. Return to your dashboard and try again.');
}
if (auth_is_signed_in()) {
    try {
        $db = getMySQLi();
        auth_record_event($db, (int)$_SESSION['auth_uid'], 'logout');
        $db->close();
    } catch (Throwable $e) { error_log('Practical 10 logout log failed: ' . $e->getMessage()); }
}
auth_revoke_browser_token();
auth_erase_session();
header('Location: login.php?status=logout');
exit;
