<?php

require_once __DIR__ . '/functions.php';

function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    
    return getCurrentUser();
}

function requireAdmin() {
    $user = requireAuth();
    
    if (!isAdmin()) {
        require_once __DIR__ . '/logger.php';
        logSecurity("Unauthorized admin access attempt", ['user_id' => $user['id']]);
        showError("Access Denied: Admin privileges required", 403);
    }
    
    return $user;
}

function ensureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function loginUser($user) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
}

function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
