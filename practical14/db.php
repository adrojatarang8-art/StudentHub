<?php
/** StudentHub PDO connection. Defaults match a fresh local XAMPP installation.
 * For a deployed site, configure DB_HOST, DB_NAME, DB_USER and DB_PASS in the server environment.
 */
function getPDO(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $database = getenv('DB_NAME') ?: 'studenthub_db';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS');
    if ($password === false) { $password = ''; } // typical local XAMPP default only
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
