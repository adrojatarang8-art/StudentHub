<?php
/** Shared role-aware navigation. Authorization must still be enforced on each page. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
function p14_nav(array $account, string $active = ''): void {
    $role = (string)($account['role'] ?? 'student');
    $items = $role === 'admin' ? [
        ['index.php','Home'],
        ['admin.php','Admin Dashboard'],
        ['student_directory.php','Manage Students'],
        ['ajax_events.php','AJAX Events'],
        ['event_management.php','Posters & Events'],
        ['records.php','Form Records'],
        ['events.html','Public Events']
    ] : [
        ['index.php','Home'],
        ['dashboard.php','My Dashboard'],
        ['events.html','Events'],
        ['timetable.html','Timetable'],
        ['attendance.html','Attendance']
    ];
    echo '<nav class="p14-menu" aria-label="Role-based menu">';
    foreach ($items as $item) {
        $aria = $active === $item[0] ? ' aria-current="page"' : '';
        echo '<a href="' . auth_e($item[0]) . '"' . $aria . '>' . auth_e($item[1]) . '</a>';
    }
    echo '<span class="p14-user">' . auth_e((string)$account['username']) . ' · ' . auth_e(ucfirst($role)) . '</span></nav>';
}
