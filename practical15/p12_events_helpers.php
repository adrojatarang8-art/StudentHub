<?php
/** StudentHub Practical 12: reusable MySQLi and poster-upload helpers. */
declare(strict_types=1);
const P12_MAX_POSTER_BYTES = 2097152; // 2 MiB; check on the server
const P12_CATEGORIES = ['Technical', 'Workshop', 'Seminar', 'Cultural', 'Sports', 'Other'];

function p12_db_query(mysqli $db, string $sql, string $types = '', array $args = []): mysqli_result {
    $stmt = $db->prepare($sql);
    if ($types !== '') {
        $refs = [];
        foreach ($args as $key => $value) $refs[$key] = &$args[$key];
        $stmt->bind_param($types, ...$refs);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if (!$result) throw new RuntimeException('Database query did not return a result.');
    return $result;
}

function p12_db_write(mysqli $db, string $sql, string $types, array $args): int {
    $stmt = $db->prepare($sql);
    $refs = [];
    foreach ($args as $key => $value) $refs[$key] = &$args[$key];
    $stmt->bind_param($types, ...$refs);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed;
}

function p12_post_text(string $key, int $max): string {
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) throw new DomainException('Invalid form submission.');
    $value = trim($value);
    if ($value === '' || (function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) > $max) {
        throw new DomainException(ucwords(str_replace('_', ' ', $key)) . ' must be filled in (max ' . $max . ' characters).');
    }
    return $value;
}

function p12_validate_fields(): array {
    $title = p12_post_text('title', 150);
    $date = p12_post_text('event_date', 10);
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) throw new DomainException('Please enter a real event date.');
    $venue = p12_post_text('venue', 150);
    $category = p12_post_text('category', 50);
    if (!in_array($category, P12_CATEGORIES, true)) throw new DomainException('Select a valid event category.');
    $description = p12_post_text('description', 2000);
    $status = (string)($_POST['event_status'] ?? '');
    if (!in_array($status, ['open', 'closed'], true)) throw new DomainException('Select Open or Closed status.');
    return [$title, $date, $venue, $category, $description, $status];
}

/** Validate upload fully before moving it; return null when there is no poster. */
function p12_validate_poster(): ?array {
    if (!isset($_FILES['poster']) || (int)($_FILES['poster']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $file = $_FILES['poster'];
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) throw new DomainException('Invalid poster upload.');
    $err = (int)$file['error'];
    if ($err !== UPLOAD_ERR_OK) {
        if (in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) throw new DomainException('Poster is too large. Maximum size is 2 MB.');
        throw new DomainException('Poster upload failed. Select a valid image and try again.');
    }
    $size = (int)($file['size'] ?? -1);
    $tmp = (string)($file['tmp_name'] ?? '');
    if ($size <= 0 || $size > P12_MAX_POSTER_BYTES) throw new DomainException('Poster must be under 2 MB and must not be empty.');
    if (!is_uploaded_file($tmp)) throw new DomainException('Invalid uploaded file source.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!is_string($mime) || !isset($allowed[$mime])) throw new DomainException('Only JPG, PNG, or WEBP poster images are allowed.');
    $dimension = @getimagesize($tmp);
    if (!$dimension || !isset($dimension[0], $dimension[1]) || (int)$dimension[0] > 6000 || (int)$dimension[1] > 6000 || (int)$dimension[0] < 1 || (int)$dimension[1] < 1 || ($dimension['mime'] ?? '') !== $mime) {
        throw new DomainException('Poster content must be a valid image of reasonable dimensions.');
    }
    return ['tmp' => $tmp, 'extension' => $allowed[$mime]];
}

function p12_move_poster(array $upload): string {
    $folder = __DIR__ . '/uploads/event_posters';
    if (!is_dir($folder) || !is_writable($folder)) throw new RuntimeException('Poster upload folder is missing or not writable.');
    $relative = 'uploads/event_posters/' . bin2hex(random_bytes(16)) . '.' . $upload['extension'];
    if (!move_uploaded_file($upload['tmp'], __DIR__ . '/' . $relative)) {
        throw new RuntimeException('Could not save the poster to the upload folder.');
    }
    chmod(__DIR__ . '/' . $relative, 0644);
    return $relative;
}

function p12_safe_poster_path(?string $relative): ?string {
    if ($relative === null || !preg_match('~^uploads/event_posters/[a-f0-9]{32}\.(?:jpg|png|webp)$~D', $relative)) return null;
    return $relative;
}

function p12_remove_poster(?string $relative): void {
    $safe = p12_safe_poster_path($relative);
    if ($safe !== null && is_file(__DIR__ . '/' . $safe)) @unlink(__DIR__ . '/' . $safe);
}

function p12_log(mysqli $db, int $userId, ?int $eventId, string $action, string $details): void {
    p12_db_write($db, 'INSERT INTO event_audit_log(event_id, actor_user_id, action, details) VALUES (?, ?, ?, ?)', 'iiss', [$eventId, $userId, $action, (function_exists('mb_substr') ? mb_substr($details, 0, 255, 'UTF-8') : substr($details, 0, 255))]);
}
function p12_go(string $notice): void {
    header('Location: event_management.php?notice=' . rawurlencode($notice), true, 303);
    exit;
}
