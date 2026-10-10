<?php
session_start();
require_once __DIR__ . '/mysqli_db.php';
if (empty($_SESSION['account_csrf'])) $_SESSION['account_csrf'] = bin2hex(random_bytes(32));
function e($x): string { return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }
$errors = []; $success = $_SESSION['account_success'] ?? '';
unset($_SESSION['account_success']);
$old = ['full_name'=>'','username'=>'','email'=>'','mobile'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $_) $old[$key] = trim((string)($_POST[$key] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (!hash_equals($_SESSION['account_csrf'], (string)($_POST['csrf_token'] ?? ''))) $errors[] = 'Invalid security token. Refresh and try again.';
    if (!preg_match('/^[\p{L}][\p{L} .\x27-]{2,99}$/u', $old['full_name'])) $errors[] = 'Full name must be 3–100 letters/characters.';
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{2,39}$/', $old['username'])) $errors[] = 'Username must be 3–40 characters and start with a letter (letters, digits, underscore).';
    if (strlen($old['email']) > 254 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $old['mobile'])) $errors[] = 'Mobile number must contain exactly 10 digits.';
    if (strlen($password) < 8 || strlen($password) > 72 || !preg_match('/[A-Z]/',$password) || !preg_match('/[a-z]/',$password) || !preg_match('/[0-9]/',$password) || !preg_match('/[^A-Za-z0-9]/',$password)) $errors[] = 'Password must be 8–72 characters and include uppercase, lowercase, a number and a special character.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (!$errors) {
        try {
            $db = getMySQLi();
            // Friendly checks; unique DB indexes remain final duplicate protection for concurrent submits.
            $check = $db->prepare('SELECT username, email FROM user_accounts WHERE username = ? OR email = ? LIMIT 2');
            $check->bind_param('ss', $old['username'], $old['email']);
            $check->execute();
            $found = $check->get_result();
            while ($existing = $found->fetch_assoc()) {
                if ($existing['username'] === $old['username']) $errors[] = 'Username is already taken.';
                if ($existing['email'] === $old['email']) $errors[] = 'Email is already registered.';
            }
            $check->close();
            if (!$errors) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->begin_transaction();
                try {
                    $stmt = $db->prepare('INSERT INTO user_accounts (full_name, username, email, mobile, password_hash) VALUES (?, ?, ?, ?, ?)');
                    $stmt->bind_param('sssss', $old['full_name'], $old['username'], $old['email'], $old['mobile'], $hash);
                    $stmt->execute();
                    $userId = $db->insert_id;
                    $stmt->close();
                    $action = 'account_registered';
                    $audit = $db->prepare('INSERT INTO account_audit_log (user_id, action) VALUES (?, ?)');
                    $audit->bind_param('is', $userId, $action);
                    $audit->execute(); $audit->close();
                    $db->commit();
                    $_SESSION['account_csrf'] = bin2hex(random_bytes(32));
                    $_SESSION['account_success'] = 'Account created successfully! Your password was stored securely.';
                    header('Location: secure_account.php'); exit;
                } catch (mysqli_sql_exception $ex) {
                    $db->rollback();
                    if ($ex->getCode() === 1062) $errors[] = 'Email or username already registered.';
                    else { error_log('Account insert failed: '.$ex->getMessage()); $errors[] = 'Unable to create account right now. Please try again.'; }
                }
            }
            $db->close();
        } catch (mysqli_sql_exception $ex) {
            error_log('Account database connection failed: '.$ex->getMessage());
            $errors[] = 'Database not ready. Import secure_accounts.sql into studenthub_db and start MySQL in XAMPP.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>StudentHub | Create Account</title><link rel="stylesheet" href="css/style.css"></head>
<body><header><h1>StudentHub Portal</h1><nav><a href="index.html">Home</a><a href="register.php">Student Registration</a><a href="secure_account.php">Create Account</a><a href="practical8.php">Event Registration</a><a href="db_test.php">Database Status</a></nav></header>
<main><section class="p8-panel account-panel"><h2>Create Student Account</h2><p>Secure account registration with duplicate checks and encrypted password hashes.</p>
<?php if ($success): ?><div class="p8-success" role="status"><?=e($success)?></div><?php endif; ?>
<?php if ($errors): ?><div class="p8-error" role="alert"><strong>Please correct:</strong><ul><?php foreach (array_unique($errors) as $err): ?><li><?=e($err)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form id="secureAccountForm" method="post" action="secure_account.php" novalidate>
<input type="hidden" name="csrf_token" value="<?=e($_SESSION['account_csrf'])?>">
<label for="full_name">Full Name</label><input required id="full_name" name="full_name" minlength="3" maxlength="100" autocomplete="name" value="<?=e($old['full_name'])?>" placeholder="Enter full name">
<label for="username">Username</label><input required id="username" name="username" minlength="3" maxlength="40" pattern="[A-Za-z][A-Za-z0-9_]{2,39}" autocomplete="username" value="<?=e($old['username'])?>" placeholder="e.g. student_01">
<label for="email">Email</label><input required type="email" id="email" name="email" maxlength="254" autocomplete="email" value="<?=e($old['email'])?>" placeholder="student@example.com">
<label for="mobile">Mobile Number</label><input required id="mobile" name="mobile" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" autocomplete="tel" value="<?=e($old['mobile'])?>" placeholder="10-digit mobile">
<label for="password">Password</label><input required type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" placeholder="Strong password"><small>8–72 characters: uppercase, lowercase, number and special character.</small>
<label for="confirm_password">Confirm Password</label><input required type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" placeholder="Re-enter password">
<button type="submit">Create Secure Account</button></form>
<p><small>Existing Student Registration form (CSV/JSON) is still available under Student Registration.</small></p>
</section></main><script src="js/secureAccount.js" defer></script></body></html>
