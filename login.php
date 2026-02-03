<?php
require 'db.php';
require 'includes/auth.php';
require 'includes/functions.php';
require 'includes/csrf.php';
require 'includes/logger.php';

ensureSession();

$error_msg = "";
$success_msg = "";

if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success_msg = "Account created successfully! Please login.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_msg = "Please fill in all fields.";
        logSecurity("Login attempt with empty fields");
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            logLogin($username, true);
            redirect('index.php');
        } else {
            $error_msg = "Invalid username or password.";
            logLogin($username, false);
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