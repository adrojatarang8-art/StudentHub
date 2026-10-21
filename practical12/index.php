<?php
// ======================================================
// index.php - Event Management Admin Dashboard
// ======================================================

require_once 'db.php';

// Search and Filter handling
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR venue LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($status !== '') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY event_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Portal - Admin</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .poster-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }
        .poster-thumb:hover {
            transform: scale(1.08);
        }
        .poster-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.4rem;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <i class="bi bi-calendar2-check-fill text-primary me-2 fs-4"></i>
            Event Management System
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="add_event.php" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Add New Event
            </a>
        </div>
    </div>
</nav>

<div class="container my-4">

    <!-- Flash Messages -->
    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
            <span><?= htmlspecialchars($_GET['msg']) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5 align-middle"></i>
            <span><?= htmlspecialchars($_GET['error']) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter & Search Toolbar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="index.php" class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title or venue..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-lg-3 col-md-5">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="Technical" <?= $category === 'Technical' ? 'selected' : '' ?>>Technical</option>
                        <option value="Cultural" <?= $category === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                        <option value="Sports" <?= $category === 'Sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="Workshop" <?= $category === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                        <option value="Seminar" <?= $category === 'Seminar' ? 'selected' : '' ?>>Seminar</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-5">
                    <select name="status" class="form-select">
                        <option value="">All Statuses (Open / Closed)</option>
                        <option value="Open" <?= $status === 'Open' ? 'selected' : '' ?>>Open</option>
                        <option value="Closed" <?= $status === 'Closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="bi bi-funnel-fill"></i> Filter</button>
                    <?php if ($search !== '' || $category !== '' || $status !== ''): ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Events List Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-calendar-event me-2 text-primary"></i>Events List
            </h5>
            <span class="badge bg-secondary rounded-pill px-3 py-2">Total: <?= count($events) ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-muted">
                    <tr>
                        <th style="width: 80px;" class="ps-3">Poster</th>
                        <th>Event Details</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted fw-semibold">No events found matching your search or filters.</span>
                                <div class="mt-3">
                                    <a href="add_event.php" class="btn btn-sm btn-outline-primary">+ Add New Event</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if (!empty($event['poster']) && file_exists(__DIR__ . '/uploads/' . $event['poster'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($event['poster']) ?>" 
                                             alt="Poster" 
                                             class="poster-thumb"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#posterModal<?= $event['id'] ?>"
                                             title="Click to preview">
                                    <?php else: ?>
                                        <div class="poster-placeholder" title="No poster uploaded">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($event['title']) ?></div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;">
                                        <?= htmlspecialchars($event['description'] ?: 'No description provided.') ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                        <?= htmlspecialchars($event['category']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-secondary">
                                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                                        <?= date('M d, Y', strtotime($event['event_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <?= htmlspecialchars($event['venue']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($event['status'] === 'Open'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>Open
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-dash-circle-fill me-1"></i>Closed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                                        <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-outline-primary" title="Edit Event">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="delete_event.php?id=<?= $event['id'] ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete event &quot;<?= addslashes(htmlspecialchars($event['title'])) ?>&quot;? This will also remove the poster file.');" 
                                           title="Delete Event">
                                            <i class="bi bi-trash3-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Poster Preview Modal -->
                            <?php if (!empty($event['poster']) && file_exists(__DIR__ . '/uploads/' . $event['poster'])): ?>
                                <div class="modal fade" id="posterModal<?= $event['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold text-dark">
                                                    <i class="bi bi-image me-2 text-primary"></i><?= htmlspecialchars($event['title']) ?>
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center p-2 bg-light">
                                                <img src="uploads/<?= htmlspecialchars($event['poster']) ?>" alt="Event Poster" class="img-fluid rounded" style="max-height: 480px; width: auto;">
                                            </div>
                                            <div class="modal-footer py-2">
                                                <a href="uploads/<?= htmlspecialchars($event['poster']) ?>" download class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download me-1"></i> Download Poster
                                                </a>
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
