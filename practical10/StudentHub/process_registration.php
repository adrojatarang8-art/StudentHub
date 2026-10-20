<?php
session_start();

function clean_input(string $value): string
{
    return trim(strip_tags($value));
}

function render_result(string $title, array $messages, bool $success): void
{
    $class = $success ? 'p7-success-box' : 'p7-error-box';
    $heading = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>StudentHub | Practical 7 Result</title>';
    echo '<link rel="stylesheet" href="css/style.css"></head><body>';
    echo '<main><section class="p7-result-card">';
    echo "<h2>{$heading}</h2><div class=\"{$class}\"><ul>";

    foreach ($messages as $message) {
        echo '<li>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</li>';
    }

    echo '</ul></div>';
    echo '<div class="p7-actions">';
    echo '<a class="p7-link-button" href="register.php">Back to Registration</a>';
    if ($success) {
        echo '<a class="p7-link-button" href="records.php">View Stored Records</a>';
    }
    echo '</div></section></main></body></html>';
}

// 1. POST handling
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    render_result('Invalid Request', ['Please submit the registration form using POST.'], false);
    exit;
}

// Advanced extension: CSRF validation
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if ($sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(403);
    render_result('Security Check Failed', ['Invalid or expired CSRF token. Open the registration form and try again.'], false);
    exit;
}

// 2. Read + sanitize input
$name = clean_input($_POST['name'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$mobile = preg_replace('/\D+/', '', $_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$course = clean_input($_POST['course'] ?? '');
$year = clean_input($_POST['year'] ?? '');
$gender = clean_input($_POST['gender'] ?? '');
$termsAccepted = isset($_POST['terms']);

$errors = [];

// 3. Server-side validation
if ($name === '') {
    $errors[] = 'Full name is required.';
} elseif (strlen($name) < 3 || strlen($name) > 50 || !preg_match("/^[A-Za-z]+(?:[ '\\-][A-Za-z]+)*$/", $name)) {
    $errors[] = 'Name must contain 3 to 50 valid letters.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}

if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
    $errors[] = 'Enter a valid 10-digit Indian mobile number.';
}

$passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^])[A-Za-z\d@$!%*?&#^]{8,}$/';
if (!preg_match($passwordPattern, $password)) {
    $errors[] = 'Password must be 8+ characters and include uppercase, lowercase, number and special character.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Password and confirm password do not match.';
}

$allowedCourses = ['B.Tech', 'M.Tech', 'B.Com', 'B.Sc'];
if (!in_array($course, $allowedCourses, true)) {
    $errors[] = 'Please select a valid course.';
}

$allowedYears = ['1', '2', '3', '4'];
if (!in_array($year, $allowedYears, true)) {
    $errors[] = 'Please select a valid year.';
}

$allowedGenders = ['Male', 'Female', 'Other'];
if (!in_array($gender, $allowedGenders, true)) {
    $errors[] = 'Please select a valid gender.';
}

if (!$termsAccepted) {
    $errors[] = 'You must accept the Terms and Conditions.';
}

if ($errors) {
    http_response_code(422);
    render_result('Registration Failed', $errors, false);
    exit;
}

// 4. Prepare safe record. Password is never stored as plain text.
$record = [
    'created_at' => date('Y-m-d H:i:s'),
    'name' => $name,
    'email' => $email,
    'mobile' => $mobile,
    'course' => $course,
    'year' => $year,
    'gender' => $gender,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
];

$dataDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'data';
$csvPath = $dataDirectory . DIRECTORY_SEPARATOR . 'registrations.csv';
$jsonPath = $dataDirectory . DIRECTORY_SEPARATOR . 'registrations.json';

if (!is_dir($dataDirectory) && !mkdir($dataDirectory, 0775, true)) {
    http_response_code(500);
    render_result('Storage Error', ['Could not create the data folder.'], false);
    exit;
}

// 5. Safe CSV append with file lock
$csvHandle = fopen($csvPath, 'c+');
if ($csvHandle === false) {
    http_response_code(500);
    render_result('Storage Error', ['Could not open CSV storage file.'], false);
    exit;
}

$csvWritten = false;
if (flock($csvHandle, LOCK_EX)) {
    fseek($csvHandle, 0, SEEK_END);

    if (ftell($csvHandle) === 0) {
        fputcsv($csvHandle, array_keys($record));
    }

    $csvWritten = fputcsv($csvHandle, array_values($record)) !== false;
    fflush($csvHandle);
    flock($csvHandle, LOCK_UN);
}
fclose($csvHandle);

if (!$csvWritten) {
    http_response_code(500);
    render_result('Storage Error', ['Could not write the record to CSV.'], false);
    exit;
}

// 6. Safe JSON append with file lock
$jsonHandle = fopen($jsonPath, 'c+');
if ($jsonHandle === false) {
    http_response_code(500);
    render_result('Storage Error', ['CSV was written, but JSON file could not be opened.'], false);
    exit;
}

$jsonWritten = false;
if (flock($jsonHandle, LOCK_EX)) {
    rewind($jsonHandle);
    $existingText = stream_get_contents($jsonHandle);
    $records = json_decode($existingText ?: '[]', true);

    if (!is_array($records)) {
        $records = [];
    }

    $records[] = $record;
    $encoded = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($encoded !== false) {
        rewind($jsonHandle);
        ftruncate($jsonHandle, 0);
        $jsonWritten = fwrite($jsonHandle, $encoded . PHP_EOL) !== false;
        fflush($jsonHandle);
    }

    flock($jsonHandle, LOCK_UN);
}
fclose($jsonHandle);

if (!$jsonWritten) {
    http_response_code(500);
    render_result('Storage Error', ['CSV was written, but JSON storage failed.'], false);
    exit;
}

// Rotate token after successful POST.
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

render_result(
    'Registration Successful',
    [
        'Form was submitted using POST.',
        'Inputs were validated and sanitized on the server.',
        'Record was safely added to registrations.csv.',
        'Record was safely added to registrations.json.'
    ],
    true
);
