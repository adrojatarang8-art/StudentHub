<?php
/** Practical 11: Student management CRUD + search/filter/pagination/soft delete. */
declare(strict_types=1);
require_once __DIR__ . '/p11_student_helpers.php';
$currentUser = auth_require_role('admin');
$db = null;
$dbError = '';
$formError = '';
$editingId = 0;
$defaults = ['enrollment'=>'', 'full_name'=>'', 'email'=>'', 'course'=>'B.Tech CE',
             'semester'=>'3', 'city'=>'', 'skill'=>''];
$form = $defaults;
$stats = ['total' => 0, 'active_count' => 0, 'deleted_count' => 0];
$filters = p11_filters($_GET);
$rows = [];
$courses = [];
$totalFiltered = 0;
$page = 1;
$totalPages = 1;
$flash = [
  'created' => 'Student record added successfully.',
  'updated' => 'Student record updated successfully.',
  'deleted' => 'Student soft-deleted successfully. Its database record is preserved.',
  'restored' => 'Student record restored successfully.',
  'unchanged' => 'Nothing changed. The record may already have this status.',
];
$notice = $flash[(string)($_GET['notice'] ?? '')] ?? '';

try {
    $db = getMySQLi();
    // A clear installation message is more helpful than a blank 500 page.
    $db->query('SELECT student_id, enrollment, full_name, email, course, semester, city, skill, status, deleted_at FROM students LIMIT 0');
} catch (Throwable $e) {
    error_log('Practical 11 database initialization: ' . $e->getMessage());
    $dbError = 'Database or Practical 11 columns are missing. Start MySQL and import practical11.sql (or use Practical11_Full_Install.sql on a fresh setup).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($dbError !== '') {
        $formError = $dbError;
    } elseif (!auth_check_csrf((string)($_POST['csrf_token'] ?? ''))) {
        $formError = 'Security check failed. Refresh and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        $id = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        try {
            if ($action === 'delete' || $action === 'restore') {
                if ($id < 1) throw new DomainException('Invalid student ID.');
                if ($action === 'delete') {
                    if (($_POST['confirm_delete'] ?? '') !== 'yes') {
                        throw new DomainException('Please confirm the deletion first.');
                    }
                    $changed = p11_execute($db,
                        "UPDATE students SET status = 'deleted', deleted_at = NOW() WHERE student_id = ? AND status = 'active'",
                        'i', [$id]);
                } else {
                    $changed = p11_execute($db,
                        "UPDATE students SET status = 'active', deleted_at = NULL WHERE student_id = ? AND status = 'deleted'",
                        'i', [$id]);
                }
                p11_notice_redirect($changed === 1 ? ($action === 'delete' ? 'deleted' : 'restored') : 'unchanged');
            } elseif ($action === 'create' || $action === 'update') {
                $editingId = $action === 'update' ? (int)$id : 0;
                if ($action === 'update' && $id < 1) throw new DomainException('Invalid student ID.');
                foreach ($defaults as $field => $default) {
                    $form[$field] = trim((string)($_POST[$field] ?? ''));
                }
                $email = strtolower($form['email']);
                $form['email'] = $email;
                $semester = filter_var($form['semester'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
                if (!preg_match('/^[A-Za-z0-9-]{4,20}$/D', $form['enrollment'])) {
                    throw new DomainException('Enrollment must contain 4–20 letters, digits or hyphens.');
                }
                if (!preg_match("/^[\\p{L}][\\p{L} .'\\-]{1,99}$/uD", $form['full_name'])) {
                    throw new DomainException('Enter a valid full name (2–100 characters).');
                }
                if (($action === 'create' && $email === '') || ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254))) {
                    throw new DomainException('Enter a valid student email address.');
                }
                if ($form['course'] === '' || strlen($form['course']) > 60 || $semester === false ||
                    $form['city'] === '' || strlen($form['city']) > 80 ||
                    $form['skill'] === '' || strlen($form['skill']) > 80) {
                    throw new DomainException('Enter valid course, semester (1–12), city and skill details.');
                }
                if ($action === 'create') {
                    p11_execute($db, 'INSERT INTO students
                        (enrollment, full_name, email, course, semester, city, skill, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, \'active\')',
                        'ssssiss', [$form['enrollment'], $form['full_name'], $email, $form['course'], (int)$semester, $form['city'], $form['skill']]);
                    p11_notice_redirect('created');
                } else {
                    $changed = p11_execute($db, 'UPDATE students SET enrollment=?, full_name=?, email=?,
                        course=?, semester=?, city=?, skill=? WHERE student_id=?',
                        'ssssissi', [$form['enrollment'], $form['full_name'], $email === '' ? null : $email,
                         $form['course'], (int)$semester, $form['city'], $form['skill'], $id]);
                    p11_notice_redirect($changed > 0 ? 'updated' : 'unchanged');
                }
            } else {
                throw new DomainException('Unknown action.');
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Practical 11 write error: ' . $e->getMessage());
            $formError = (int)$e->getCode() === 1062
                ? 'Enrollment or email already exists. Please enter a unique value.'
                : 'Could not save the record. Please check the database and try again.';
        } catch (DomainException $e) {
            $formError = $e->getMessage();
        } catch (Throwable $e) {
            error_log('Practical 11 unexpected write error: ' . $e->getMessage());
            $formError = 'Operation failed. Please check your inputs and try again.';
        }
    }
}

if ($dbError === '' && $db instanceof mysqli) {
    try {
        // Load existing student when Edit is clicked, unless a failed POST has supplied the form values.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['edit'])) {
            $editingId = filter_var($_GET['edit'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 0;
            if ($editingId > 0) {
                $editRow = p11_select($db, 'SELECT enrollment,full_name,email,course,semester,city,skill FROM students WHERE student_id=? LIMIT 1', 'i', [$editingId])->fetch_assoc();
                if ($editRow) $form = array_replace($form, $editRow);
                else { $editingId = 0; $formError = 'Student record was not found.'; }
            }
        }
        $statsRow = $db->query("SELECT COUNT(*) AS total, COALESCE(SUM(status='active'),0) AS active_count,
            COALESCE(SUM(status='deleted'),0) AS deleted_count FROM students")->fetch_assoc();
        if ($statsRow) $stats = $statsRow;
        $courses = $db->query('SELECT DISTINCT course FROM students ORDER BY course ASC')->fetch_all(MYSQLI_ASSOC);
        [$where, $types, $values] = p11_conditions($filters);
        $countRow = p11_select($db, 'SELECT COUNT(*) AS total FROM students' . $where, $types, $values)->fetch_assoc();
        $totalFiltered = (int)($countRow['total'] ?? 0);
        $perPage = 10;
        $totalPages = max(1, (int)ceil($totalFiltered / $perPage));
        $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $page = min($requestedPage, $totalPages);
        $offset = ($page - 1) * $perPage;
        $rows = p11_select($db,
            'SELECT student_id, enrollment, full_name, email, course, semester, city, skill, status
             FROM students' . $where . ' ORDER BY student_id DESC LIMIT ? OFFSET ?',
            $types . 'ii', array_merge($values, [$perPage, $offset]))->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $e) {
        error_log('Practical 11 list error: ' . $e->getMessage());
        $dbError = 'Could not load student records. Please check practical11.sql and your MySQL connection.';
    }
    $db->close();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Practical 11 | Student Management - StudentHub</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="p10-body p11-body">
<header class="p10-header">
  <h1>StudentHub <span class="p11-brand-dot">/</span> Student Management</h1>
  <nav aria-label="Main navigation">
    <a href="index.html">Home</a>
    <a href="admin.php">Admin Dashboard</a>
    <a href="student_directory.php" aria-current="page">Manage Students</a>
    <a href="events.html">Events</a>
    <span class="p11-nav-user">Signed in: <?=auth_e($currentUser['username'])?> (Admin)</span>
  </nav>
</header>
<main class="p11-shell">
  <div class="p11-intro">
    <div><div class="p10-eyebrow">PRACTICAL 11 · PHP &amp; MySQLi</div>
    <h2>Student Directory</h2>
    <p>Manage student records, quickly find entries and export filtered information.</p></div>
    <div class="p11-top-actions"><a class="p11-outline-btn" href="student_directory.php">+ New Student</a>
    <a class="p11-export" href="student_export.php?<?=auth_e(http_build_query($filters, '', '&', PHP_QUERY_RFC3986))?>">↓ Export CSV</a></div>
  </div>
  <?php if ($notice !== ''): ?><div class="p10-notice" role="status"><?=auth_e($notice)?></div><?php endif; ?>
  <?php if ($dbError !== ''): ?><div class="p10-error" role="alert"><?=auth_e($dbError)?></div><?php endif; ?>
  <?php if ($formError !== ''): ?><div class="p10-error" role="alert"><?=auth_e($formError)?></div><?php endif; ?>
  <?php if ($dbError === ''): ?>
  <div class="p11-stats" aria-label="Student statistics">
    <div class="p11-stat"><span>Total Records</span><strong><?=auth_e($stats['total'])?></strong></div>
    <div class="p11-stat"><span>Active Students</span><strong><?=auth_e($stats['active_count'])?></strong></div>
    <div class="p11-stat"><span>Soft Deleted</span><strong><?=auth_e($stats['deleted_count'])?></strong></div>
    <div class="p11-stat"><span>Matching Filters</span><strong><?=auth_e($totalFiltered)?></strong></div>
  </div>
  <div class="p11-grid">
    <section class="p11-panel p11-edit-card" id="student-form">
      <div class="p11-panel-top"><span class="p10-eyebrow"><?=($editingId > 0) ? 'EDIT EXISTING RECORD' : 'CREATE NEW RECORD'?></span>
        <?php if ($editingId > 0): ?><a href="student_directory.php">Cancel</a><?php endif; ?></div>
      <h3><?=($editingId > 0) ? 'Edit Student #'.auth_e($editingId) : 'Add New Student'?></h3>
      <p class="p11-subtitle">All fields are validated before MySQLi saves the record.</p>
      <form action="student_directory.php" method="post" class="p11-form">
        <input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>">
        <input type="hidden" name="action" value="<?=($editingId > 0) ? 'update' : 'create'?>">
        <?php if ($editingId > 0): ?><input type="hidden" name="student_id" value="<?=auth_e($editingId)?>"><?php endif; ?>
        <label for="enrollment">Enrollment Number <span>*</span></label>
        <input id="enrollment" type="text" name="enrollment" maxlength="20" pattern="[A-Za-z0-9-]{4,20}" required value="<?=auth_e($form['enrollment'])?>" placeholder="25CE201">
        <label for="full_name">Full Name <span>*</span></label>
        <input id="full_name" type="text" name="full_name" maxlength="100" required value="<?=auth_e($form['full_name'])?>" placeholder="Student full name">
        <label for="email">Email <?=($editingId > 0) ? '(optional for older records)' : '*'?></label>
        <input id="email" type="email" name="email" maxlength="254" <?=($editingId > 0) ? '' : 'required'?> value="<?=auth_e($form['email'])?>" placeholder="student@example.com">
        <div class="p11-form-row"><div><label for="course">Course <span>*</span></label>
          <select id="course" name="course" required>
            <?php foreach (['B.Tech CE', 'B.Tech CSE', 'B.Tech IT', 'BCA', 'MCA', 'Other'] as $candidate): ?>
              <option value="<?=auth_e($candidate)?>" <?=($form['course'] === $candidate) ? 'selected' : ''?>><?=auth_e($candidate)?></option>
            <?php endforeach; ?>
            <?php if (!in_array((string)$form['course'], ['B.Tech CE', 'B.Tech CSE', 'B.Tech IT', 'BCA', 'MCA', 'Other'], true)): ?>
              <option selected value="<?=auth_e($form['course'])?>"><?=auth_e($form['course'])?></option>
            <?php endif; ?>
          </select></div><div><label for="semester">Semester <span>*</span></label>
          <input id="semester" type="number" min="1" max="12" name="semester" required value="<?=auth_e($form['semester'])?>"></div></div>
        <div class="p11-form-row"><div><label for="city">City <span>*</span></label>
          <input id="city" type="text" name="city" maxlength="80" required value="<?=auth_e($form['city'])?>" placeholder="Ahmedabad"></div>
          <div><label for="skill">Main Skill <span>*</span></label>
          <input id="skill" type="text" name="skill" maxlength="80" required value="<?=auth_e($form['skill'])?>" placeholder="PHP"></div></div>
        <button type="submit" class="p11-save"><?=($editingId > 0) ? 'Save Changes' : '+ Add Student'?></button>
      </form>
    </section>
    <section class="p11-panel p11-list-card">
      <div class="p11-panel-top"><div><span class="p10-eyebrow">DATABASE RECORDS</span><h3>All Students</h3></div>
        <span class="p11-count"><?=auth_e($totalFiltered)?> result(s)</span></div>
      <form action="student_directory.php" method="get" class="p11-filters">
        <div class="p11-field-search"><label for="q">Search by name, email, course or ID</label>
          <input id="q" type="search" name="q" value="<?=auth_e($filters['q'])?>" placeholder="Search students..."></div>
        <div><label for="course_filter">Course</label><select id="course_filter" name="course">
          <option value="">All Courses</option>
          <?php foreach ($courses as $item): $c = (string)$item['course']; ?>
            <option value="<?=auth_e($c)?>" <?=($filters['course'] === $c) ? 'selected' : ''?>><?=auth_e($c)?></option>
          <?php endforeach; ?></select></div>
        <div><label for="status_filter">Record Status</label><select id="status_filter" name="status">
          <option value="active" <?=$filters['status'] === 'active' ? 'selected' : ''?>>Active</option>
          <option value="deleted" <?=$filters['status'] === 'deleted' ? 'selected' : ''?>>Deleted</option>
          <option value="all" <?=$filters['status'] === 'all' ? 'selected' : ''?>>All</option></select></div>
        <div class="p11-filter-buttons"><button type="submit">Apply</button>
          <a href="student_directory.php">Reset</a></div>
      </form>
      <div class="p11-tablewrap"><table class="p11-table">
        <thead><tr><th>ID / Enrollment</th><th>Student</th><th>Course / Sem</th><th>City / Skill</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr><td colspan="6" class="p11-empty">No students match the selected filters.</td></tr><?php else: ?>
        <?php foreach ($rows as $student): ?>
          <tr><td><b>#<?=auth_e($student['student_id'])?></b><small><?=auth_e($student['enrollment'])?></small></td>
              <td><b><?=auth_e($student['full_name'])?></b><small><?=auth_e($student['email'] ?: 'No email on older record')?></small></td>
              <td><?=auth_e($student['course'])?><small>Semester <?=auth_e($student['semester'])?></small></td>
              <td><?=auth_e($student['city'])?><small><?=auth_e($student['skill'])?></small></td>
              <td><span class="p11-pill <?=($student['status'] === 'active') ? 'p11-active' : 'p11-deleted'?>"><?=($student['status'] === 'active') ? 'Active' : 'Deleted'?></span></td>
              <td><div class="p11-row-actions"><a href="student_directory.php?edit=<?=auth_e($student['student_id'])?>#student-form">Edit</a>
              <?php if ($student['status'] === 'active'): ?>
                <form action="student_directory.php" method="post" onsubmit="return confirm('Soft delete this student? You can restore the record later.');">
                  <input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>">
                  <input type="hidden" name="action" value="delete"><input type="hidden" name="student_id" value="<?=auth_e($student['student_id'])?>">
                  <input type="hidden" name="confirm_delete" value="yes"><button class="p11-delete" type="submit">Delete</button></form>
              <?php else: ?>
                <form action="student_directory.php" method="post"><input type="hidden" name="csrf_token" value="<?=auth_e(auth_csrf())?>">
                <input type="hidden" name="action" value="restore"><input type="hidden" name="student_id" value="<?=auth_e($student['student_id'])?>">
                <button class="p11-restore" type="submit">Restore</button></form>
              <?php endif; ?></div></td></tr>
        <?php endforeach; endif; ?>
        </tbody></table></div>
      <div class="p11-list-footer"><span>Page <?=auth_e($page)?> of <?=auth_e($totalPages)?> · 10 records per page</span>
        <div class="p11-pages">
        <?php if ($page > 1): ?><a href="<?=auth_e(p11_link($filters, $page - 1))?>">← Previous</a><?php endif; ?>
        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
          <a class="<?=$p === $page ? 'selected' : ''?>" href="<?=auth_e(p11_link($filters, $p))?>"><?=auth_e($p)?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="<?=auth_e(p11_link($filters, $page + 1))?>">Next →</a><?php endif; ?>
        </div></div>
      <p class="p11-subtitle">Delete marks records as deleted; it does not remove them or their linked registrations. Switch the status filter to “Deleted” to restore.</p>
    </section>
  </div>
  <?php endif; ?>
  <p class="p11-footer-note">Practical 11 · CRUD (INSERT / SELECT / UPDATE / soft DELETE) · Prepared MySQLi statements · Admin access only</p>
</main>
<footer><p>© 2026 StudentHub Portal · Practical 11</p></footer>
</body>
</html>
