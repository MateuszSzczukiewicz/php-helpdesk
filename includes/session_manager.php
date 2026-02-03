<?php

declare(strict_types=1);

define('SESSION_TIMEOUT', 1800);

function initSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
    }
    
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 86400) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > SESSION_TIMEOUT) {
            require_once __DIR__ . '/logger.php';
            logSecurity("Session timeout", [
                'user' => $_SESSION['username'] ?? 'unknown',
                'inactive_seconds' => $inactive
            ]);
            
            session_unset();
            session_destroy();
            
            header("Location: login.php?timeout=1");
            exit();
        }
    }
    
    $_SESSION['last_activity'] = time();
}

function getSessionTimeRemaining(): int
{
    if (!isset($_SESSION['last_activity'])) {
        return SESSION_TIMEOUT;
    }
    
    $elapsed = time() - $_SESSION['last_activity'];
    $remaining = SESSION_TIMEOUT - $elapsed;
    
    return max(0, $remaining);
}

function refreshSession(): void
{
    $_SESSION['last_activity'] = time();
}
