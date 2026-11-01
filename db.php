<?php
require_once __DIR__ . '/auth.php';
$host = getenv('DB_HOST') ?: '127.0.0.1';
$database = getenv('DB_NAME') ?: 'chisel';
$port = filter_var(getenv('DB_PORT') ?: '3306', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
if ($port === false) app_fail(500, 'Database port is not configured correctly.');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

if ($username === false || $username === '' || $password === false || $password === '') {
    app_fail(500, 'Database credentials are not configured.');
}

try {
    $conn = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $error) {
    error_log('Database connection failed.');
    app_fail(500, 'Database unavailable.');
}
