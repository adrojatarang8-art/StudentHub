<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
auth_initialize();
if (auth_is_signed_in()) {
    header('Location: ' . auth_landing_page((string)$_SESSION['auth_role']));
    exit;
}
$error = '';
$identity = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim((string)($_POST['identity'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';
    if (!auth_check_csrf((string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Security check failed. Refresh the page and try again.';
    } elseif ($identity === '' || $password === '' || strlen($identity) > 254) {
        $error = 'Enter your username/email and password.';
    } else {
        try {
            $db = getMySQLi();
            $stmt = $db->prepare('SELECT user_id, full_name, role, status, password_hash FROM user_accounts
                                   WHERE username = ? OR email = ? LIMIT 1');
            $stmt->bind_param('ss', $identity, $identity);
            $stmt->execute();
            $account = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($account && ($account['status'] ?? 'active') === 'active' && password_verify($password, (string)$account['password_hash'])) {
                $uid = (int)$account['user_id'];
                $stamp = $db->prepare('UPDATE user_accounts SET last_login_at = NOW() WHERE user_id = ?');
                $stamp->bind_param('i', $uid);
                $stamp->execute();
                $stamp->close();
                auth_record_event($db, $uid, 'login_success');
                auth_establish_session($account);
                // Do not leave an older browser token active on subsequent login.
                if (isset($_COOKIE[AUTH_REMEMBER_COOKIE])) auth_revoke_browser_token();
                if ($remember) {
                    try { auth_save_remember_token($db, $uid); }
                    catch (Throwable $e) { error_log('Could not set remember-me token: ' . $e->getMessage()); }
                }
                $db->close();
                header('Location: ' . auth_landing_page((string)$account['role']));
                exit;
            }
            auth_record_event($db, $account ? (int)$account['user_id'] : null, 'login_failed');
            $error = ($account && ($account['status'] ?? '') === 'inactive')
                ? 'This account is inactive. Contact an administrator.'
                : 'Invalid username/email or password.';
            $db->close();
        } catch (Throwable $e) {
            error_log('Practical 10 login error: ' . $e->getMessage());
            $error = 'Database unavailable. Check XAMPP MySQL and import practical15.sql into studenthub_db.';
        }
    }
}
$status = (string)($_GET['status'] ?? '');
$notice = match ($status) {
    'logout' => 'You have logged out successfully.',
    'timeout' => 'Your session expired after 15 minutes of inactivity. Please log in again.',
    'login_required' => 'Please log in before opening your dashboard.',
    default => ''
};
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>StudentHub | Secure Login</title><link rel="stylesheet" href="css/style.css"></head><body class="p10-body">
<header class="p10-header"><h1>StudentHub Portal</h1><nav><a href="index.php">Home</a><a href="secure_account.php">Create Account</a><a href="login.php" aria-current="page">Login</a></nav></header>
<main class="p10-shell"><section class="p10-card"><div class="p10-eyebrow">PRACTICAL 10 · AUTHENTICATION</div>
<h2>Welcome back</h2><p class="p10-muted">Sign in securely using your StudentHub account.</p>
<?php if ($notice !== ''): ?><div class="p10-notice" role="status"><?=auth_e($notice)?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="p10-error" role="alert"><?=auth_e($error)?></div><?php endif; ?>
<form action="login.php" method="POST">
<input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>">
<label for="identity">Username or Email</label><input id="identity" name="identity" type="text" maxlength="254" autocomplete="username" required value="<?=auth_e($identity)?>" placeholder="Enter username or email">
<label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Enter password">
<label class="p10-check" for="remember_me"><input type="checkbox" name="remember_me" id="remember_me" value="1"> <span>Remember me for 30 days on this device</span></label>
<button class="p10-primary" type="submit">Secure Login →</button></form>
<p class="p10-muted p10-small">No account? <a href="secure_account.php">Create a student account</a>. Admin access is granted separately by the database administrator.</p>
</section><aside class="p10-aside"><h3>Security features</h3><ul><li>Encrypted password verification with password_verify()</li><li>Session ID regeneration after successful login</li><li>15-minute inactivity timeout</li><li>Separate admin and student dashboards</li><li>Optional rotating remember-me token</li></ul></aside></main>
<footer><p>© 2026 StudentHub Portal · Practical 10</p></footer></body></html>
