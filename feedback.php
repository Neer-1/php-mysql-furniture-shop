<?php
// Check if the status parameter is set in the URL
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
        }

        .message {
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em;
        }

        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="feedback">
        <?php if ($status == 'success'): ?>
            <p class="message">Registration Successful! Welcome to our website.</p>
        <?php else: ?>
            <p class="message">Registration Failed! Please try again.</p>
        <?php endif; ?>

        <a href="login.php" class="button">log in</a>
    </div>

</body>
</html>
