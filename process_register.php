<?php
require_once 'auth.php';
require_once 'input.php';
start_app_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
require_valid_csrf();
require_once 'db.php';

$firstName = trim(post_text('first_name', 50));
$lastName = trim(post_text('last_name', 50));
$username = trim(post_text('username', 40));
$email = trim(post_text('email', 254));
$password = post_text('password', 72);
$confirmPassword = post_text('confirm_password', 72);
$phone = trim(post_text('phone', 15));

$valid = $firstName !== '' && $lastName !== ''
    && preg_match('/^[A-Za-z0-9_]{3,40}$/', $username) === 1
    && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
    && preg_match('/^5\d{7}$/', $phone) === 1
    && strlen($password) >= 12
    && hash_equals($password, $confirmPassword);

if (!$valid) {
    header('Location: feedback.php?status=failure');
    exit();
}

$check = $conn->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
$check->execute(['username' => $username, 'email' => $email]);
if ($check->fetch()) {
    header('Location: feedback.php?status=failure');
    exit();
}

try {
    $insert = $conn->prepare(
        'INSERT INTO users (first_name, last_name, username, email, password, phone) '
        . 'VALUES (:first_name, :last_name, :username, :email, :password, :phone)'
    );
    $insert->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'phone' => $phone,
    ]);
    header('Location: feedback.php?status=success');
} catch (PDOException $error) {
    error_log('Registration failed.');
    header('Location: feedback.php?status=failure');
}
exit();
