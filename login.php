<?php

declare(strict_types=1);
require 'db.php';
require 'includes/auth.php';
require 'includes/functions.php';
require 'includes/csrf.php';
require 'includes/logger.php';
require 'includes/rate_limiter.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$error_msg = "";
$success_msg = "";

if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error_msg = "Your session has expired due to inactivity. Please login again.";
}

if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success_msg = "Account created successfully! Please login.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $clientIP = getClientIP();

    $rateCheck = checkRateLimit($clientIP, 'login', 5, 900);
    
    if (!$rateCheck['allowed']) {
        $minutes = ceil($rateCheck['retry_after'] / 60);
        $error_msg = "Too many login attempts. Please try again in {$minutes} minutes.";
        logSecurity("Rate limit exceeded for login", ['ip' => $clientIP]);
    } elseif (empty($username) || empty($password)) {
        $error_msg = "Please fill in all fields.";
        logSecurity("Login attempt with empty fields");
    } else {
        recordRateLimitAttempt($clientIP, 'login');
        
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            clearRateLimit($clientIP, 'login');
            loginUser($user);
            logLogin($username, true);
            redirect('index.php');
        } else {
            $error_msg = "Invalid username or password.";
            logLogin($username, false);
            
            if ($rateCheck['remaining'] > 0) {
                $error_msg .= " ({$rateCheck['remaining']} attempts remaining)";
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
    <title>Login - Helpdesk System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <h2 style="text-align: center;">Login to Helpdesk</h2>

        <?php if (!empty($success_msg)): ?>
            <div class="success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <?php csrfField(); ?>
            
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" style="width: 100%; margin-top: 10px;">Sign In</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>

</body>

</html>