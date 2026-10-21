<?php
/** Convenience route used in the practical guide; admin.php is the canonical dashboard. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
auth_require_role('admin');
header('Location: admin.php', true, 302);
exit;
