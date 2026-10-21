<?php
// ======================================================
// delete_event.php - Safely Delete Event & Poster File
// ======================================================

require_once 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php?error=" . urlencode("Invalid or missing event ID."));
    exit;
}

try {
    // 1. Fetch event record to know the title & poster filename
    $stmt = $pdo->prepare("SELECT title, poster FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    if ($event) {
        $uploadDir = __DIR__ . '/uploads/';

        // 2. Unlink (delete) the poster file from the server disk if exists
        if (!empty($event['poster'])) {
            $filePath = $uploadDir . $event['poster'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // 3. Delete event record from database
        $deleteStmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $deleteStmt->execute([$id]);

        // 4. Log the deletion in audit table
        addAuditLog($pdo, $id, 'DELETE', "Event '{$event['title']}' and its poster were deleted.");

        header("Location: index.php?msg=" . urlencode("Event '{$event['title']}' was deleted successfully!"));
        exit;
    } else {
        header("Location: index.php?error=" . urlencode("Event record does not exist or was already deleted."));
        exit;
    }
} catch (PDOException $e) {
    header("Location: index.php?error=" . urlencode("Database Error while deleting: " . $e->getMessage()));
    exit;
}
?>
