<?php

declare(strict_types=1);

require 'db.php';
require 'includes/auth.php';
require 'includes/csrf.php';
require 'includes/validation.php';
require 'includes/logger.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields!";
        logSecurity("Registration attempt with empty fields");
    } else {
        $usernameValidation = validateUsername($username);
        if (!$usernameValidation->isValid()) {
            $error_msg = $usernameValidation->getErrorMessage();
        } elseif (!validateEmail($email)) {
            $error_msg = "Please enter a valid email address.";
        } else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation->isValid()) {
                $error_msg = $passwordValidation->getErrorMessage();
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);

                if ($stmt->rowCount() > 0) {
                    $error_msg = "This username or email is already taken.";
                    logSecurity("Registration attempt with duplicate username/email", ['username' => $username]);
                } else {
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

<div class="container container--narrow container--centered">
    <h2 class="text-center">Create Account</h2>

    <?php if (!empty($error_msg)): ?>
        <div class="error"><?= $error_msg ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <?php csrfField(); ?>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+">
        <small class="form-hint">3-20 characters, letters, numbers, and underscores only</small>

        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required minlength="8">
        <small class="form-hint">At least 8 characters, must include letters and numbers</small>

        <button type="submit" class="btn--full mt-sm">Register</button>
    </form>

    <p class="text-center mt-md">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>
