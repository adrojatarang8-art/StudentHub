<?php
/** MySQLi connection for secure student accounts. Configure environment in production. */
function getMySQLi(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS');
    if ($password === false) $password = ''; // XAMPP default, local use only
    $name = getenv('DB_NAME') ?: 'studenthub_db';
    $db = new mysqli($host, $user, $password, $name);
    $db->set_charset('utf8mb4');
    return $db;
}
