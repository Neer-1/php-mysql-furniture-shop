<?php
// Later remediation: do not expose database details or request data in error logs.
ini_set('display_errors', '0');
function app_fail(int $status, string $message): void
{
    http_response_code($status);
    $json = false;
    foreach (headers_list() as $header) {
        if (stripos($header, 'Content-Type: application/json') === 0) $json = true;
    }
    if ($json) {
        echo json_encode(['status' => 'error', 'success' => false, 'message' => $message]);
    } else {
        echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    exit();
}
set_exception_handler(function (Throwable $error): void {
    error_log('A11 unhandled ' . get_class($error) . '; request failed.');
    app_fail(500, 'Request failed. Please try again later.');
});

function require_existing_account(int $id, bool $admin): void
{
    global $conn;
    require_once __DIR__ . '/db.php';
    // Table choice is internal; never interpolate a client-supplied identifier.
    $query = $admin ? 'SELECT id FROM admin WHERE id = :id' : 'SELECT id FROM users WHERE id = :id';
    $check = $conn->prepare($query);
    $check->execute(['id' => $id]);
    if ($check->fetchColumn() === false) {
        $_SESSION = [];
        session_regenerate_id(true);
        app_fail($admin ? 403 : 401, 'Sign in is required.');
    }
}

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    session_set_cookie_params([
        'lifetime' => 0,
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    header('Cache-Control: no-store');
    session_start();
    $now = time();
    if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
        if (!isset($_SESSION['created_at'], $_SESSION['last_seen'])
            || $now - (int)$_SESSION['last_seen'] > 1800
            || $now - (int)$_SESSION['created_at'] > 28800) {
            $_SESSION = [];
            session_regenerate_id(true);
        } else {
            $_SESSION['last_seen'] = $now;
        }
    }
}

function csrf_token(): string
{
    start_app_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function require_valid_csrf(): void
{
    start_app_session();
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        app_fail(403, 'Invalid request token. Reload the page and try again.');
    }
}

function require_user(): int
{
    start_app_session();
    if (empty($_SESSION['user_id']) || !is_int($_SESSION['user_id'])) {
        app_fail(401, 'Sign in is required.');
    }
    require_existing_account($_SESSION['user_id'], false);
    return $_SESSION['user_id'];
}

function require_admin(): int
{
    start_app_session();
    if (empty($_SESSION['admin_id']) || !is_int($_SESSION['admin_id'])) {
        app_fail(403, 'Administrator sign in is required.');
    }
    require_existing_account($_SESSION['admin_id'], true);
    return $_SESSION['admin_id'];
}