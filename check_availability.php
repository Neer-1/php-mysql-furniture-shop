<?php
require_once 'auth.php';
require_once 'input.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
require_valid_csrf();
require_once 'db.php';
$field = isset($_POST['username']) ? 'username' : 'email';
$value = trim(post_text($field, $field === 'username' ? 40 : 254));
if (($field === 'username' && preg_match('/^[A-Za-z0-9_]{3,40}$/', $value) !== 1)
    || ($field === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false)) {
    http_response_code(422); echo json_encode(['message' => 'Enter a valid value.']); exit();
}
try {
    // Column is selected from two literals, never from request data.
    $query = $field === 'username' ? 'SELECT id FROM users WHERE username = :value LIMIT 1'
        : 'SELECT id FROM users WHERE email = :value LIMIT 1';
    $stmt = $conn->prepare($query);
    $stmt->execute(['value' => $value]);
    echo json_encode(['available' => $stmt->fetchColumn() === false]);
} catch (PDOException $error) {
    error_log('Availability check failed.');
    http_response_code(500); echo json_encode(['message' => 'Availability check failed.']);
}
