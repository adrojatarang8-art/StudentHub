<?php
/**
 * Practical 13 - shared JSON response, admin session, CSRF and MySQLi helpers.
 * Only called by endpoints under /api. All errors return JSON, never HTML.
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/auth.php';

function p13_json(int $status, array $payload): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function p13_fail(int $status, string $message, string $code): void {
    p13_json($status, ['success' => false, 'error' => ['code' => $code, 'message' => $message]]);
}

function p13_method(string $required): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $required) {
        header('Allow: ' . $required);
        p13_fail(405, 'This endpoint requires ' . $required . '.', 'METHOD_NOT_ALLOWED');
    }
}

/** Confirm the active account is still an administrator in the database. */
function p13_admin(): mysqli {
    auth_start();
    if (!auth_check_timeout()) {
        p13_fail(401, 'Session expired. Sign in again.', 'SESSION_EXPIRED');
    }
    if (!auth_is_signed_in()) {
        // Existing Remember Me support. Still verify role with the database below.
        auth_restore_remember_token();
    }
    if (!auth_is_signed_in()) {
        p13_fail(401, 'Please log in as an administrator.', 'LOGIN_REQUIRED');
    }
    try {
        $db = getMySQLi();
        $uid = (int)$_SESSION['auth_uid'];
        $stmt = $db->prepare('SELECT role FROM user_accounts WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$result || $result['role'] !== 'admin') {
            $db->close();
            p13_fail(403, 'Only administrator accounts may use this API.', 'FORBIDDEN');
        }
        return $db;
    } catch (Throwable $e) {
        error_log('P13 authentication/database failure: ' . $e->getMessage());
        p13_fail(503, 'Database connection unavailable. Check Apache, MySQL and SQL setup.', 'DB_UNAVAILABLE');
    }
}

function p13_read_json(): array {
    if (stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== 0) {
        p13_fail(415, 'Send a JSON request with Content-Type: application/json.', 'CONTENT_TYPE');
    }
    $length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($length > 20000) p13_fail(413, 'JSON payload exceeds 20 KB.', 'PAYLOAD_TOO_LARGE');
    $source = file_get_contents('php://input', false, null, 0, 20001);
    if ($source === false || strlen($source) > 20000) p13_fail(413, 'JSON payload exceeds 20 KB.', 'PAYLOAD_TOO_LARGE');
    try {
        $data = json_decode($source, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        p13_fail(400, 'Invalid JSON syntax.', 'INVALID_JSON');
    }
    if (!is_array($data) || array_values($data) === $data) {
        p13_fail(400, 'A JSON object is required.', 'INVALID_BODY');
    }
    if (!auth_check_csrf((string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
        p13_fail(403, 'CSRF security token is missing or invalid. Refresh the page.', 'CSRF_FAILED');
    }
    return $data;
}

function p13_id(mixed $value): int {
    if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
        p13_fail(422, 'A positive numeric event ID is required.', 'INVALID_ID');
    }
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options'=> ['min_range'=>1]]);
    if ($id === false) p13_fail(422, 'A positive numeric event ID is required.', 'INVALID_ID');
    return (int)$id;
}

function p13_fields(array $raw): array {
    foreach (['title', 'event_date', 'venue', 'category', 'description'] as $key) {
        if (!isset($raw[$key]) || !is_string($raw[$key])) {
            p13_fail(422, 'Please provide valid text for ' . $key . '.', 'VALIDATION_ERROR');
        }
    }
    $fields = [];
    foreach (['title','event_date','venue','category','description'] as $key) {
        $fields[$key] = trim($raw[$key]);
    }
    $limits = ['title'=>150,'venue'=>150,'category'=>50,'description'=>2000];
    foreach ($limits as $key => $max) {
        $len = function_exists('mb_strlen') ? mb_strlen($fields[$key], 'UTF-8') : strlen($fields[$key]);
        if ($len < 2 || $len > $max) {
            p13_fail(422, ucfirst(str_replace('_',' ',$key)) . ' must be between 2 and ' . $max . ' characters.', 'VALIDATION_ERROR');
        }
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $fields['event_date']);
    if (!$date || $date->format('Y-m-d') !== $fields['event_date'] ||
        $fields['event_date'] < '2000-01-01' || $fields['event_date'] > '2100-12-31') {
        p13_fail(422, 'Enter a valid event date (YYYY-MM-DD).', 'VALIDATION_ERROR');
    }
    return $fields;
}

function p13_db_error(Throwable $error): void {
    error_log('Practical 13 API failure: ' . $error->getMessage());
    if ($error instanceof mysqli_sql_exception && (int)$error->getCode() === 1451) {
        p13_fail(409, 'This event has registrations and cannot be deleted.', 'EVENT_IN_USE');
    }
    p13_fail(500, 'Database operation failed. Check the SQL setup and try again.', 'DATABASE_ERROR');
}
