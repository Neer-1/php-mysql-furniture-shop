<?php
require_once 'auth.php';
require_once 'input.php';
start_app_session();
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    require_once 'db.php';
    $adminName = trim(post_text('adminInput', 100));
    $submittedPassword = post_text('password', 72);
    $stmt = $conn->prepare('SELECT id, admin, password FROM admin WHERE admin = :admin LIMIT 1');
    $stmt->execute(['admin' => $adminName]);
    $admin = $stmt->fetch();
    $validPassword = false;

    if ($admin) {
        $validPassword = password_verify($submittedPassword, $admin['password']);
        if (!$validPassword && preg_match('/^[a-f0-9]{64}$/i', $admin['password'])) {
            $legacyHash = hash('sha256', $submittedPassword);
            $validPassword = hash_equals(strtolower($admin['password']), $legacyHash);
            if ($validPassword) {
                $newHash = password_hash($submittedPassword, PASSWORD_DEFAULT);
                $upgrade = $conn->prepare('UPDATE admin SET password = :password WHERE id = :id');
                $upgrade->execute(['password' => $newHash, 'id' => $admin['id']]);
            }
        }
    }

    if ($admin && $validPassword) {
        if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT) && !preg_match('/^[a-f0-9]{64}$/i', $admin['password'])) {
            $rehash = $conn->prepare('UPDATE admin SET password = :hash WHERE id = :id');
            $rehash->execute(['hash' => password_hash($submittedPassword, PASSWORD_DEFAULT), 'id' => $admin['id']]);
        }
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['created_at'] = $_SESSION['last_seen'] = time();
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin'] = $admin['admin'];
        header('Location: admin.php');
        exit();
    }
    $errorMessage = 'Invalid administrator name or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Login as Admin</h1>
    <?php if ($errorMessage !== ''): ?><p role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
    <form method="POST" action="admin_login.php" class="login-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <label for="adminInput">Admin Name:</label>
        <input type="text" name="adminInput" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Login</button>
    </form>

    <!-- Back button -->
    <div style="margin-top: 20px;">
        <button class="back-button" onclick="window.location.href='index.html'">Back to Home</button>
    </div>
</body>
</html>
