<?php
/** Used after the server has responded with HTTP 403 from auth_require_role(). */
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>StudentHub | Access Denied</title><link rel="stylesheet" href="css/style.css"></head>
<body class="p10-body"><header class="p10-header"><h1>StudentHub Portal</h1></header><main class="p10-shell"><section class="p10-card"><div class="p10-eyebrow">HTTP 403 · ACCESS DENIED</div><h2>Access denied</h2><p>This dashboard requires a different account role. You are not authorized to view this page.</p><a href="<?=auth_e(auth_landing_page((string)($_SESSION['auth_role'] ?? 'student')))?>">Go to your dashboard</a></section></main></body></html>
