<?php
// ======================================================
// add_event.php - Add New Event with Validated Poster Upload
// ======================================================

require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $event_date  = trim($_POST['event_date'] ?? '');
    $venue       = trim($_POST['venue'] ?? '');
    $status      = trim($_POST['status'] ?? 'Open');
    $description = trim($_POST['description'] ?? '');

    // 1. Text Field Validations
    if ($title === '') {
        $errors[] = "Event title is required.";
    } elseif (strlen($title) > 150) {
        $errors[] = "Event title must not exceed 150 characters.";
    }

    $validCategories = ['Technical', 'Cultural', 'Sports', 'Workshop', 'Seminar'];
    if ($category === '' || !in_array($category, $validCategories)) {
        $errors[] = "Please select a valid event category.";
    }

    if ($event_date === '') {
        $errors[] = "Event date is required.";
    }

    if ($venue === '') {
        $errors[] = "Venue is required.";
    }

    if (!in_array($status, ['Open', 'Closed'])) {
        $status = 'Open';
    }

    $posterFilename = null;

    // 2. Poster File Upload & Comprehensive Validations
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error during file upload. Code: " . $file['error'];
        } else {
            // A. File Size Validation (Max 2MB = 2097152 bytes)
            $maxBytes = 2 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $fileSizeMB = round($file['size'] / (1024 * 1024), 2);
                $errors[] = "File size exceeds 2MB limit. (Uploaded: {$fileSizeMB} MB)";
            }

            // B. File Extension Validation
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "Invalid file extension. Only JPG, JPEG, PNG, and WEBP formats are allowed.";
            }

            // C. Server-Side MIME Type Validation (Inspection via fileinfo)
            if (empty($errors)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = "Invalid image content. The uploaded file is not a valid image.";
                }
            }

            // D. Safe Upload & Renaming (Advanced Extension)
            if (empty($errors)) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Unique filename generation
                $posterFilename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
                $destination = $uploadDir . $posterFilename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    $errors[] = "Failed to move uploaded file to target folder.";
                    $posterFilename = null;
                }
            }
        }
    }

    // 3. Database Insertion
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (title, category, event_date, venue, status, poster, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $category, $event_date, $venue, $status, $posterFilename, $description]);

            $newId = (int)$pdo->lastInsertId();
            addAuditLog($pdo, $newId, 'CREATE', "Event '{$title}' created with status '{$status}'.");

            header("Location: index.php?msg=" . urlencode("Event '{$title}' added successfully!"));
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-calendar2-check-fill text-primary me-2"></i> Event Management System
        </a>
        <a href="index.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</nav>

<div class="container my-5" style="max-width: 760px;">
    <div class="card shadow border-0 rounded-3">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-plus me-2"></i>Create New Event</h5>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following errors:</div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="add_event.php" method="POST" enctype="multipart/form-data" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Annual Tech Hackathon 2026" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <option value="Technical" <?= (($_POST['category'] ?? '') === 'Technical') ? 'selected' : '' ?>>Technical</option>
                            <option value="Cultural" <?= (($_POST['category'] ?? '') === 'Cultural') ? 'selected' : '' ?>>Cultural</option>
                            <option value="Sports" <?= (($_POST['category'] ?? '') === 'Sports') ? 'selected' : '' ?>>Sports</option>
                            <option value="Workshop" <?= (($_POST['category'] ?? '') === 'Workshop') ? 'selected' : '' ?>>Workshop</option>
                            <option value="Seminar" <?= (($_POST['category'] ?? '') === 'Seminar') ? 'selected' : '' ?>>Seminar</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Venue <span class="text-danger">*</span></label>
                        <input type="text" name="venue" class="form-control" placeholder="e.g., Seminar Hall 2" value="<?= htmlspecialchars($_POST['venue'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Event Status</label>
                        <select name="status" class="form-select">
                            <option value="Open" <?= (($_POST['status'] ?? 'Open') === 'Open') ? 'selected' : '' ?>>Open (Registrations Active)</option>
                            <option value="Closed" <?= (($_POST['status'] ?? '') === 'Closed') ? 'selected' : '' ?>>Closed (Registrations Ended)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Poster <span class="text-muted fw-normal">(Optional)</span></label>
                    <input type="file" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text text-muted">
                        <i class="bi bi-shield-check text-primary"></i> <strong>Validation Rules:</strong> Allowed formats: <code>JPG, JPEG, PNG, WEBP</code> | Maximum file size: <strong>2MB</strong>.
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Provide a detailed description of the event..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Save Event
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
