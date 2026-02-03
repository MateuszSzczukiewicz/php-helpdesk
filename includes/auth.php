<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function requireAuth(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    
    return getCurrentUser();
}

function requireAdmin(): array
{
    $user = requireAuth();
    
    if (!isAdmin()) {
        require_once __DIR__ . '/logger.php';
        logSecurity("Unauthorized admin access attempt", ['user_id' => $user['id']]);
        showError("Access Denied: Admin privileges required", 403);
    }
    
    return $user;
}

function ensureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function loginUser(array $user): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
}

function logoutUser(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            name: session_name(),
            value: '',
            expires_or_options: time() - 42000,
            path: $params["path"],
            domain: $params["domain"],
            secure: $params["secure"],
            httponly: $params["httponly"]
        );
    }
    
    session_destroy();
}
