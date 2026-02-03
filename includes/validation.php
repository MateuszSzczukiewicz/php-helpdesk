<?php

declare(strict_types=1);

require_once __DIR__ . '/ValidationResult.php';

function validateEmail(string $email): bool
{
    return $email
        |> trim(...)
        |> (fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
        |> (fn($result) => $result !== false);
}

function validatePassword(string $password): ValidationResult
{
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
    
    return empty($errors)
        ? ValidationResult::success()
        : ValidationResult::failure(implode('. ', $errors));
}

function validateUsername(string $username): ValidationResult
{
    $length = $username |> trim(...) |> strlen(...);
    
    if ($length < 3 || $length > 20) {
        return ValidationResult::failure('Username must be between 3 and 20 characters');
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ValidationResult::failure('Username can only contain letters, numbers, and underscores');
    }
    
    return ValidationResult::success();
}

function validateTicketTitle(string $title): ValidationResult
{
    $length = $title |> trim(...) |> strlen(...);
    
    return match (true) {
        $length < 5 => ValidationResult::failure('Title must be at least 5 characters long'),
        $length > 100 => ValidationResult::failure('Title must not exceed 100 characters'),
        default => ValidationResult::success(),
    };
}

function validateTicketDescription(string $description): ValidationResult
{
    $length = $description |> trim(...) |> strlen(...);
    
    return match (true) {
        $length < 10 => ValidationResult::failure('Description must be at least 10 characters long'),
        $length > 5000 => ValidationResult::failure('Description must not exceed 5000 characters'),
        default => ValidationResult::success(),
    };
}

function sanitizeInput(string $input): string
{
    return $input
        |> trim(...)
        |> (fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8'));
}
