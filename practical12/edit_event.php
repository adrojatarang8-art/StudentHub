<?php
// ======================================================
// edit_event.php - Edit Event Details and Poster Update
// ======================================================

require_once 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php?error=" . urlencode("Invalid or missing event ID."));
    exit;
}

// Fetch existing record
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: index.php?error=" . urlencode("Event not found with ID: " . $id));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $event_date  = trim($_POST['event_date'] ?? '');
    $venue       = trim($_POST['venue'] ?? '');
    $status      = trim($_POST['status'] ?? 'Open');
    $description = trim($_POST['description'] ?? '');
    $removePoster = !empty($_POST['remove_poster']);

    // Validations
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

    $posterFilename = $event['poster'];
    $uploadDir = __DIR__ . '/uploads/';

    // If user selected to remove existing poster without uploading new one
    if ($removePoster && empty($_FILES['poster']['name'])) {
        if (!empty($event['poster']) && file_exists($uploadDir . $event['poster'])) {
            unlink($uploadDir . $event['poster']);
        }
        $posterFilename = null;
    }

    // Process new poster upload if provided
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error during file upload. Code: " . $file['error'];
        } else {
            // Check Size (Max 2MB)
            $maxBytes = 2 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $errors[] = "Poster must be less than 2MB. Current: " . round($file['size'] / (1024 * 1024), 2) . " MB.";
            }

            // Check Extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "Allowed formats: JPG, JPEG, PNG, WEBP.";
            }

            // Check MIME
            if (empty($errors)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $errors[] = "Uploaded file content is not a recognized image.";
                }
            }

            // Save new poster & unlink old one
            if (empty($errors)) {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newPosterName = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
                $destination = $uploadDir . $newPosterName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Remove old poster file
                    if (!empty($event['poster']) && file_exists($uploadDir . $event['poster'])) {
                        unlink($uploadDir . $event['poster']);
                    }
                    $posterFilename = $newPosterName;
                } else {
                    $errors[] = "Could not save uploaded poster on server.";
                }
            }
        }
    }

    // Update database
    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE events SET title = ?, category = ?, event_date = ?, venue = ?, status = ?, poster = ?, description = ? WHERE id = ?");
            $updateStmt->execute([$title, $category, $event_date, $venue, $status, $posterFilename, $description, $id]);

            addAuditLog($pdo, $id, 'UPDATE', "Event '{$title}' updated.");

            header("Location: index.php?msg=" . urlencode("Event '{$title}' updated successfully!"));
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
    <title>Edit Event #<?= $event['id'] ?></title>
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
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Event #<?= $event['id'] ?></h5>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following:</div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="edit_event.php?id=<?= $event['id'] ?>" method="POST" enctype="multipart/form-data" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? $event['title']) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <?php $selectedCat = $_POST['category'] ?? $event['category']; ?>
                        <select name="category" class="form-select" required>
                            <option value="Technical" <?= $selectedCat === 'Technical' ? 'selected' : '' ?>>Technical</option>
                            <option value="Cultural" <?= $selectedCat === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                            <option value="Sports" <?= $selectedCat === 'Sports' ? 'selected' : '' ?>>Sports</option>
                            <option value="Workshop" <?= $selectedCat === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                            <option value="Seminar" <?= $selectedCat === 'Seminar' ? 'selected' : '' ?>>Seminar</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($_POST['event_date'] ?? $event['event_date']) ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Venue <span class="text-danger">*</span></label>
                        <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($_POST['venue'] ?? $event['venue']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Event Status</label>
                        <?php $selectedStat = $_POST['status'] ?? $event['status']; ?>
                        <select name="status" class="form-select">
                            <option value="Open" <?= $selectedStat === 'Open' ? 'selected' : '' ?>>Open (Registrations Active)</option>
                            <option value="Closed" <?= $selectedStat === 'Closed' ? 'selected' : '' ?>>Closed (Registrations Ended)</option>
                        </select>
                    </div>
                </div>

                <!-- Current Poster Preview & Change -->
                <div class="mb-3 p-3 bg-light rounded border">
                    <label class="form-label fw-semibold d-block">Current Poster</label>
                    <?php if (!empty($event['poster']) && file_exists(__DIR__ . '/uploads/' . $event['poster'])): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="uploads/<?= htmlspecialchars($event['poster']) ?>" alt="Current Poster" class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                            <div>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($event['poster']) ?></div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="remove_poster" value="1" id="removePosterCheck">
                                    <label class="form-check-label text-danger" for="removePosterCheck">
                                        <i class="bi bi-trash"></i> Remove current poster (without replacement)
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i>No poster uploaded for this event currently.</p>
                    <?php endif; ?>

                    <label class="form-label fw-semibold text-secondary">Upload New / Replacement Poster</label>
                    <input type="file" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text text-muted">
                        Leave blank to retain current poster. Allowed: JPG, PNG, WEBP (Max 2MB).
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? $event['description']) ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-dark px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check2-all me-1"></i> Update Event
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
