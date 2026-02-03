<?php

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = "Password must contain at least one letter";
    }
    
    if (!preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return [
        'valid' => empty($errors),
        'message' => implode('. ', $errors)
    ];
}

function validateUsername($username) {
    if (strlen($username) < 3 || strlen($username) > 20) {
        return [
            'valid' => false,
            'message' => 'Username must be between 3 and 20 characters'
        ];
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return [
            'valid' => false,
            'message' => 'Username can only contain letters, numbers, and underscores'
        ];
    }
    
    return ['valid' => true, 'message' => ''];
}

function validateTicketTitle($title) {
    $length = strlen(trim($title));
    
    if ($length < 5) {
        return [
            'valid' => false,
            'message' => 'Title must be at least 5 characters long'
        ];
    }
    
    if ($length > 100) {
        return [
            'valid' => false,
            'message' => 'Title must not exceed 100 characters'
        ];
    }
    
    return ['valid' => true, 'message' => ''];
}

function validateTicketDescription($description) {
    $length = strlen(trim($description));
    
    if ($length < 10) {
        return [
            'valid' => false,
            'message' => 'Description must be at least 10 characters long'
        ];
    }
    
    if ($length > 5000) {
        return [
            'valid' => false,
            'message' => 'Description must not exceed 5000 characters'
        ];
    }
    
    return ['valid' => true, 'message' => ''];
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
