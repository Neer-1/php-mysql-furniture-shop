<?php
require_once 'auth.php';
start_app_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h2>User Registration</h2>
    <p>Demo only: use synthetic details. Passwords must contain 12?72 UTF-8 bytes.</p>

    <form id="registrationForm" action="process_register.php" method="POST" onsubmit="return validatePasswords();">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <label for="first_name">First Name:</label>
    <input type="text" name="first_name" id="first_name" required>

    <label for="last_name">Last Name:</label>
    <input type="text" name="last_name" id="last_name" required>

    <label for="username">Username:</label>
    <input type="text" name="username" id="username" minlength="3" maxlength="40" pattern="[A-Za-z0-9_]+" required>
    <span id="username_status"></span>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>
    <span id="email_status"></span>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" minlength="12" maxlength="72" autocomplete="new-password" required>

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" minlength="12" maxlength="72" autocomplete="new-password" required>

    <label for="phone">Phone Number:</label>
    <input type="tel" name="phone" id="phone" inputmode="numeric" pattern="5[0-9]{7}" required>
    <span id="phone-error" class="error-message" style="display:none;"></span>


    <button type="submit">Register</button>
</form>

    <!-- Back button -->
    <div style="margin-top: 20px;">
    <button class="back-button" onclick="window.location.href='index.html'">Back to Home</button>
    </div>


    <script>
        // JavaScript function to validate that the password and confirm password fields match
        function validatePasswords() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return false; // Prevent form submission
            }
            return true; // Proceed with form submission
        }

        for (const field of ['username', 'email']) {
            const input = document.getElementById(field);
            let generation = 0;
            input.addEventListener('blur', async () => {
                const current = ++generation;
                const value = input.value;
                const status = document.getElementById(field + '_status');
                status.textContent = 'Checking...';
                try {
                    const response = await fetch('check_availability.php', {
                        method: 'POST', credentials: 'same-origin',
                        body: new URLSearchParams({[field]: value, csrf_token: document.querySelector('[name="csrf_token"]').value})
                    });
                    if (!response.ok) throw new Error('Availability check unavailable.');
                    const data = await response.json();
                    if (typeof data.available !== 'boolean') throw new Error('Invalid response.');
                    if (current === generation && input.value === value) status.textContent = data.available ? 'Available' : 'Already in use';
                } catch (error) {
                    if (current === generation && input.value === value) status.textContent = 'Unable to check. Registration will validate this value.';
                }
            });
        }
    /*phone num check*/
    document.getElementById('phone').addEventListener('input', function() {
    const phoneInput = this.value;
    const phoneError = document.getElementById('phone-error');

    // Regular expression to match the format 5XXXXXXX

    const regex = /^5\d{7}$/;
    // const regex = /^5 \d{3} \d{4}$/;

    if (!regex.test(phoneInput)) {
        phoneError.textContent = "Please enter a valid 8 digits phone number in the format: 5XXXXXXX";
        phoneError.style.display = "block"; // Show the error message
    } else {
        phoneError.textContent = ""; // Clear error message
        phoneError.style.display = "none"; // Hide error message
    }
});

    </script>

</body>
</html>
