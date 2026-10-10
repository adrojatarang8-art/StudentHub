<?php
$jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'registrations.json';
$records = [];
$error = '';

if (is_file($jsonPath)) {
    $json = file_get_contents($jsonPath);
    $decoded = json_decode($json ?: '[]', true);

    if (is_array($decoded)) {
        $records = array_reverse($decoded);
    } else {
        $error = 'Stored JSON file could not be read.';
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Stored Registrations</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="script.js" defer></script>
</head>
<body>
<header>
    <h1>StudentHub Portal</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="register.php">Register</a>
        <a href="records.php">Stored Records</a>
    </nav>
    <hr>
</header>

<main>
    <section>
        <h2> Stored Registration Records</h2>
        <p>This page reads records from <strong>data/registrations.json</strong>.</p>

        <?php if ($error !== ''): ?>
            <div class="p7-error-box"><?php echo e($error); ?></div>
        <?php elseif (!$records): ?>
            <div class="p7-info-box">No registration records are stored yet.</div>
        <?php else: ?>
            <div class="p7-table-wrap">
                <table class="p7-table">
                    <thead>
                    <tr>
                        <th>No.</th>
                        <th>Date & Time</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Gender</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($records as $index => $record): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo e((string)($record['created_at'] ?? '')); ?></td>
                            <td><?php echo e((string)($record['name'] ?? '')); ?></td>
                            <td><?php echo e((string)($record['email'] ?? '')); ?></td>
                            <td><?php echo e((string)($record['mobile'] ?? '')); ?></td>
                            <td><?php echo e((string)($record['course'] ?? '')); ?></td>
                            <td><?php echo e((string)($record['year'] ?? '')); ?></td>
                            <td><?php echo e((string)($record['gender'] ?? '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer><hr><p>© 2026 StudentHub Portal</p></footer>
</body>
</html>
