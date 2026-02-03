<?php
require 'db.php';
require 'includes/auth.php';
require 'includes/functions.php';
require 'includes/csrf.php';
require 'includes/validation.php';
require 'includes/logger.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    requireCSRFToken();
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields!";
        logSecurity("Registration attempt with empty fields");
    } else {
        // Validate username
        $usernameValidation = validateUsername($username);
        if (!$usernameValidation['valid']) {
            $error_msg = $usernameValidation['message'];
        }
        // Validate email
        elseif (!validateEmail($email)) {
            $error_msg = "Please enter a valid email address.";
        }
        // Validate password
        else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                $error_msg = $passwordValidation['message'];
            } else {
                // Check for existing user
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);

                if ($stmt->rowCount() > 0) {
                    $error_msg = "This username or email is already taken.";
                    logSecurity("Registration attempt with duplicate username/email", ['username' => $username]);
                } else {
                    // Create user
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
                    $stmt = $conn->prepare($sql);

                    try {
                        if ($stmt->execute([$username, $email, $password_hash])) {
                            logInfo("New user registered", ['username' => $username]);
                            redirect('login.php?registered=1');
                        }
                    } catch (PDOException $e) {
                        $error_msg = "An error occurred during registration.";
                        logDatabaseError($sql, $e->getMessage());
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <h2 style="text-align: center;">Create Account</h2>

        <?php if (!empty($error_msg)): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <?php csrfField(); ?>
            
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+">
            <small style="color: #666; font-size: 0.85em;">3-20 characters, letters, numbers, and underscores only</small>

            <label for="email">Email Address:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required minlength="8">
            <small style="color: #666; font-size: 0.85em;">At least 8 characters, must include letters and numbers</small>

            <button type="submit" style="width: 100%; margin-top: 10px;">Register</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>

</body>

</html>