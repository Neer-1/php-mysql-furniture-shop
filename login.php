<?php
require_once 'auth.php';
require_once 'input.php';
start_app_session();
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    require_once 'db.php';
    $identifier = trim(post_text('userInput', 254));
    $submittedPassword = post_text('password', 72);
    $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = :username OR email = :email LIMIT 1');
    $stmt->execute(['username' => $identifier, 'email' => $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($submittedPassword, $user['password'])) {
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $rehash = $conn->prepare('UPDATE users SET password = :hash WHERE id = :id');
            $rehash->execute(['hash' => password_hash($submittedPassword, PASSWORD_DEFAULT), 'id' => $user['id']]);
        }
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['created_at'] = $_SESSION['last_seen'] = time();
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user'] = $user['username'];
        header('Location: shop.php');
        exit();
    }
    $errorMessage = 'Invalid username/email or password.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Login to Your Account</h1>
    <?php if ($errorMessage !== ''): ?><p role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
    <form method="POST" action="login.php" class="login-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <label for="userInput">Username or Email:</label>
        <input type="text" name="userInput" required><br><br>
        
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


<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Login to Your Account</h1>
<form method="POST" action="login.php" class="login-form">
    <label for="userInput">Username, Email, or UserID:</label>
    <input type="text" name="userInput" required><br><br>
    
    <label for="password">Password:</label>
    <input type="password" name="password" required><br><br>
    
    <button type="submit">Login</button>
</form>
</body>
</html> -->
