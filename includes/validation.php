<?php
/**
 * Input Validation Functions
 * 
 * Provides validation for user inputs
 */

/**
 * Validate email format
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * 
 * Requirements:
 * - At least 8 characters long
 * - Contains at least one letter
 * - Contains at least one number
 * 
 * @param string $password The password to validate
 * @return array ['valid' => bool, 'message' => string]
 */
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

/**
 * Validate username
 * 
 * Requirements:
 * - 3-20 characters long
 * - Only letters, numbers, and underscores
 * 
 * @param string $username The username to validate
 * @return array ['valid' => bool, 'message' => string]
 */
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

/**
 * Validate ticket title
 * 
 * @param string $title The title to validate
 * @return array ['valid' => bool, 'message' => string]
 */
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

/**
 * Validate ticket description
 * 
 * @param string $description The description to validate
 * @return array ['valid' => bool, 'message' => string]
 */
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

/**
 * Sanitize string input
 * 
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
