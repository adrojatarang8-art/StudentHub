<?php
/** Practical 11: safe MySQLi queries and common list filters. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function p11_select(mysqli $db, string $sql, string $types = '', array $values = []): mysqli_result {
    $statement = $db->prepare($sql);
    if ($types !== '') {
        $statement->bind_param($types, ...$values);
    }
    $statement->execute();
    $result = $statement->get_result();
    $statement->close();
    return $result;
}

function p11_execute(mysqli $db, string $sql, string $types, array $values): int {
    $statement = $db->prepare($sql);
    $statement->bind_param($types, ...$values);
    $statement->execute();
    $changed = $statement->affected_rows;
    $statement->close();
    return $changed;
}

function p11_filters(array $query): array {
    $search = trim((string)($query['q'] ?? ''));
    $search = substr($search, 0, 100);
    $course = trim((string)($query['course'] ?? ''));
    $course = substr($course, 0, 60);
    $status = (string)($query['status'] ?? 'active');
    if (!in_array($status, ['active', 'deleted', 'all'], true)) $status = 'active';
    return ['q' => $search, 'course' => $course, 'status' => $status];
}

function p11_conditions(array $filters): array {
    $parts = [];
    $types = '';
    $values = [];
    if ($filters['status'] !== 'all') {
        $parts[] = 'status = ?';
        $types .= 's';
        $values[] = $filters['status'];
    }
    if ($filters['course'] !== '') {
        $parts[] = 'course = ?';
        $types .= 's';
        $values[] = $filters['course'];
    }
    if ($filters['q'] !== '') {
        $parts[] = '(full_name LIKE ? OR email LIKE ? OR course LIKE ? OR enrollment LIKE ?)';
        $like = '%' . $filters['q'] . '%';
        $types .= 'ssss';
        array_push($values, $like, $like, $like, $like);
    }
    return [$parts ? ' WHERE ' . implode(' AND ', $parts) : '', $types, $values];
}

function p11_link(array $filters, int $page = 1): string {
    return 'student_directory.php?' . http_build_query([
        'q' => $filters['q'], 'course' => $filters['course'],
        'status' => $filters['status'], 'page' => $page,
    ], '', '&', PHP_QUERY_RFC3986);
}

function p11_notice_redirect(string $notice): void {
    header('Location: student_directory.php?notice=' . rawurlencode($notice), true, 303);
    exit;
}
