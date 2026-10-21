<?php
/**
 * Practical 10 - centralized PHP session, RBAC and persistent-login helpers.
 * Include only on PHP pages (HTML pages cannot enforce server-side authorization).
 */
declare(strict_types=1);
require_once __DIR__ . '/mysqli_db.php';

const AUTH_IDLE_TIMEOUT = 900;  // 15 minutes of inactivity (use 60 temporarily for lab test)
const AUTH_REMEMBER_DAYS = 30;
const AUTH_REMEMBER_COOKIE = 'studenthub_remember';

function auth_e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function auth_is_https(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443');
}

function auth_cookie_path(): string {
    $dir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/practical13/login.php')));
    // API endpoints live in /practical13/api, but must share ONE cookie
    // with login.php and ajax_events.php to avoid duplicate path cookies.
    if (basename($dir) === 'api') $dir = dirname($dir);
    return ($dir === '/' || $dir === '.') ? '/' : rtrim($dir, '/') . '/';
}

function auth_cookie_options(int $expires): array {
    return [
        'expires' => $expires,
        'path' => auth_cookie_path(),
        'secure' => auth_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function auth_no_cache(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

function auth_start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('STUDENTHUBSESSID');
        $sessionCookie = auth_cookie_options(0);
        unset($sessionCookie['expires']);
        $sessionCookie['lifetime'] = 0;
        session_set_cookie_params($sessionCookie);
        session_start();
        auth_no_cache();
    }
    if (empty($_SESSION['auth_csrf'])) {
        $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
    }
}

function auth_csrf(): string {
    auth_start();
    return (string)$_SESSION['auth_csrf'];
}

function auth_check_csrf(string $submitted): bool {
    return $submitted !== '' && hash_equals(auth_csrf(), $submitted);
}

function auth_is_signed_in(): bool {
    return isset($_SESSION['auth_uid']) && is_numeric($_SESSION['auth_uid']);
}

function auth_landing_page(string $role): string {
    return $role === 'admin' ? 'admin.php' : 'dashboard.php';
}

function auth_record_event(mysqli $db, ?int $userId, string $action): void {
    $stmt = $db->prepare('INSERT INTO account_audit_log (user_id, action) VALUES (?, ?)');
    $stmt->bind_param('is', $userId, $action);
    $stmt->execute();
    $stmt->close();
}

function auth_erase_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => (string)$params['path'],
            'domain' => (string)$params['domain'],
            'secure' => (bool)$params['secure'],
            'httponly' => (bool)$params['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
}

function auth_clear_remember_cookie(): void {
    setcookie(AUTH_REMEMBER_COOKIE, '', auth_cookie_options(time() - 3600));
    unset($_COOKIE[AUTH_REMEMBER_COOKIE]);
}

function auth_remember_parts(): ?array {
    $value = (string)($_COOKIE[AUTH_REMEMBER_COOKIE] ?? '');
    if (!preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/D', $value, $matches)) return null;
    return [$matches[1], $matches[2]];
}

function auth_save_remember_token(mysqli $db, int $userId): void {
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $hash = hash('sha256', $validator);
    $stmt = $db->prepare('INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
                          VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))');
    $stmt->bind_param('iss', $userId, $selector, $hash);
    $stmt->execute();
    $stmt->close();
    setcookie(AUTH_REMEMBER_COOKIE, $selector . ':' . $validator,
        auth_cookie_options(time() + AUTH_REMEMBER_DAYS * 86400));
}

function auth_revoke_browser_token(): void {
    $parts = auth_remember_parts();
    if ($parts !== null) {
        try {
            $db = getMySQLi();
            $stmt = $db->prepare('DELETE FROM remember_tokens WHERE selector = ?');
            $stmt->bind_param('s', $parts[0]);
            $stmt->execute();
            $stmt->close();
            $db->close();
        } catch (Throwable $e) {
            error_log('Practical 10 token revocation error: ' . $e->getMessage());
        }
    }
    auth_clear_remember_cookie();
}

function auth_establish_session(array $account): void {
    session_regenerate_id(true);  // critical: prevents session fixation
    $_SESSION['auth_uid'] = (int)$account['user_id'];
    $_SESSION['auth_name'] = (string)$account['full_name'];
    $_SESSION['auth_role'] = (string)$account['role'];
    $_SESSION['last_activity'] = time();
    $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
}

function auth_restore_remember_token(): void {
    $parts = auth_remember_parts();
    if ($parts === null) {
        if (isset($_COOKIE[AUTH_REMEMBER_COOKIE])) auth_clear_remember_cookie();
        return;
    }
    [$selector, $validator] = $parts;
    try {
        $db = getMySQLi();
        $stmt = $db->prepare('SELECT rt.user_id, rt.token_hash,
                      CASE WHEN rt.expires_at > NOW() THEN 1 ELSE 0 END AS token_active,
                      ua.full_name, ua.role
             FROM remember_tokens rt JOIN user_accounts ua ON ua.user_id = rt.user_id
             WHERE rt.selector = ? LIMIT 1');
        $stmt->bind_param('s', $selector);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row || (int)$row['token_active'] !== 1
            || !hash_equals((string)$row['token_hash'], hash('sha256', $validator))) {
            $db->close();
            auth_clear_remember_cookie();
            return;
        }
        // Rotate token after each successful cookie authentication; an old cookie cannot be replayed.
        $newValidator = bin2hex(random_bytes(32));
        $newHash = hash('sha256', $newValidator);
        $update = $db->prepare('UPDATE remember_tokens
            SET token_hash = ?, expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
            WHERE selector = ? AND token_hash = ? AND expires_at > NOW()');
        $oldHash = (string)$row['token_hash'];
        $update->bind_param('sss', $newHash, $selector, $oldHash);
        $update->execute();
        $updated = $update->affected_rows;
        $update->close();
        if ($updated !== 1) {
            $db->close();
            auth_clear_remember_cookie();
            return;
        }
        $uid = (int)$row['user_id'];
        $stamp = $db->prepare('UPDATE user_accounts SET last_login_at = NOW() WHERE user_id = ?');
        $stamp->bind_param('i', $uid);
        $stamp->execute();
        $stamp->close();
        auth_record_event($db, $uid, 'remember_login');
        $db->close();
        auth_establish_session($row);
        setcookie(AUTH_REMEMBER_COOKIE, $selector . ':' . $newValidator,
            auth_cookie_options(time() + AUTH_REMEMBER_DAYS * 86400));
    } catch (Throwable $e) {
        error_log('Practical 10 remember-me error: ' . $e->getMessage());
        // Fail closed when the token cannot be checked against the database.
    }
}

/** Returns false if idle timeout expired. Call before any role check. */
function auth_check_timeout(): bool {
    if (!auth_is_signed_in()) return true;
    $last = (int)($_SESSION['last_activity'] ?? 0);
    if ($last === 0 || time() - $last > AUTH_IDLE_TIMEOUT) {
        $uid = (int)$_SESSION['auth_uid'];
        try {
            $db = getMySQLi();
            auth_record_event($db, $uid, 'session_timeout');
            $db->close();
        } catch (Throwable $e) {
            error_log('Practical 10 timeout log error: ' . $e->getMessage());
        }
        auth_erase_session();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

/** Session + token restore + timeout; never silently restore on the timeout request. */
function auth_initialize(): void {
    auth_start();
    if (!auth_check_timeout()) {
        header('Location: login.php?status=timeout');
        exit;
    }
    if (!auth_is_signed_in()) auth_restore_remember_token();
}

function auth_require_role(string $requiredRole): array {
    auth_initialize();
    if (!auth_is_signed_in()) {
        header('Location: login.php?status=login_required');
        exit;
    }
    try {
        $db = getMySQLi();
        $uid = (int)$_SESSION['auth_uid'];
        $stmt = $db->prepare('SELECT user_id, full_name, username, email, role, created_at, last_login_at
                              FROM user_accounts WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $db->close();
    } catch (Throwable $e) {
        error_log('Practical 10 role verification error: ' . $e->getMessage());
        http_response_code(503);
        exit('Authentication service unavailable. Try again later.');
    }
    if (!$user) {
        auth_erase_session();
        header('Location: login.php?status=login_required');
        exit;
    }
    $_SESSION['auth_role'] = (string)$user['role'];
    $_SESSION['auth_name'] = (string)$user['full_name'];
    if ($user['role'] !== $requiredRole) {
        http_response_code(403);
        require __DIR__ . '/access_denied.php';
        exit;
    }
    return $user;
}
