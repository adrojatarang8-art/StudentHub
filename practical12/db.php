<?php
// ======================================================
// db.php - Database Configuration & Auto-Setup
// ======================================================

$host = 'localhost';
$dbname = 'event_db';
$username = 'root';
$password = ''; // Default XAMPP MySQL password is empty

try {
    // 1. Connect to MySQL server without selecting DB initially
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // 2. Automatically create database if it does not exist (Auto-Setup)
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbname}`");

    // 3. Automatically create tables if not present
    $checkTable = $pdo->query("SHOW TABLES LIKE 'events'")->fetch();
    if (!$checkTable) {
        $sqlFile = __DIR__ . '/database.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);
        }
    }

} catch (PDOException $e) {
    die("<div style='font-family: Arial, sans-serif; padding: 25px; margin: 40px auto; max-width: 650px; background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
            <h3 style='margin-top:0;'>❌ MySQL Connection Error</h3>
            <p><strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <hr style='border: 0; border-top: 1px solid #feb2b2; margin: 15px 0;'>
            <p><strong>Solution:</strong> Please ensure that <strong>MySQL is running</strong> in XAMPP Control Panel.</p>
         </div>");
}

/**
 * Add an audit log entry for changes (Advanced Extension)
 */
function addAuditLog(PDO $pdo, ?int $eventId, string $action, string $details): void {
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (event_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$eventId, $action, $details]);
    } catch (Exception $e) {
        // Silently continue if audit logging fails
    }
}
?>
