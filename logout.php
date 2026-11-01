<?php
require_once 'auth.php';
start_app_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Method not allowed.');
}
require_valid_csrf();
$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
	'expires' => time() - 42000,
	'path' => $cookie['path'],
	'domain' => $cookie['domain'],
	'secure' => $cookie['secure'],
	'httponly' => $cookie['httponly'],
	'samesite' => $cookie['samesite'] ?? 'Lax',
]);
session_destroy();
header('Location: log_out.html');
exit();
