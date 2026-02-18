<?php

declare(strict_types=1);

function generateCSRFToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function validateCSRFToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): void
{
    $token = generateCSRFToken();
    ?>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
    <?php
}

function requireCSRFToken(): void
{
    $token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        die("CSRF token validation failed. Please try again.");
    }
}
