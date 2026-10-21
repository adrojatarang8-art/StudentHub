<?php
/** Practical 11: export current filtered list as a safe Excel-compatible CSV. */
declare(strict_types=1);
require_once __DIR__ . '/p11_student_helpers.php';
auth_require_role('admin');

try {
    $db = getMySQLi();
    $filters = p11_filters($_GET);
    [$where, $types, $values] = p11_conditions($filters);
    $result = p11_select($db,
        'SELECT student_id, enrollment, full_name, email, course, semester, city, skill, status, deleted_at
         FROM students' . $where . ' ORDER BY student_id ASC', $types, $values);

    // Prevent stray whitespace/errors from corrupting the CSV download.
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="StudentHub_Practical11_Students_' . date('Ymd') . '.csv"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
    $out = fopen('php://output', 'wb');
    if ($out === false) throw new RuntimeException('Cannot open export stream');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
    fputcsv($out, ['ID', 'Enrollment', 'Full Name', 'Email', 'Course', 'Semester', 'City', 'Skill', 'Status', 'Deleted At'], ',', '"', '');
    while ($student = $result->fetch_assoc()) {
        $row = [];
        foreach (['student_id', 'enrollment', 'full_name', 'email', 'course', 'semester', 'city', 'skill', 'status', 'deleted_at'] as $field) {
            $value = (string)($student[$field] ?? '');
            // Defuse spreadsheet formulas when a user-supplied field starts with =, +, -, @, or control whitespace.
            if (preg_match('/^[\x00-\x20]*[=+\-@]/', $value)) $value = "'" . $value;
            $row[] = $value;
        }
        fputcsv($out, $row, ',', '"', '');
    }
    fclose($out);
    $db->close();
} catch (Throwable $e) {
    error_log('Practical 11 CSV export error: ' . $e->getMessage());
    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Export unavailable. Import practical11.sql, then try again.';
    }
}
