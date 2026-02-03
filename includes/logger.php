<?php

declare(strict_types=1);

function getClientIP(): string
{
    $ip = match (true) {
        !empty($_SERVER['HTTP_CF_CONNECTING_IP']) => $_SERVER['HTTP_CF_CONNECTING_IP'],
        !empty($_SERVER['HTTP_CLIENT_IP']) => $_SERVER['HTTP_CLIENT_IP'],
        !empty($_SERVER['HTTP_X_FORWARDED_FOR']) => explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0],
        !empty($_SERVER['REMOTE_ADDR']) => $_SERVER['REMOTE_ADDR'],
        default => 'unknown'
    };
    
    return $ip
        |> trim(...)
        |> (fn($trimmed) => filter_var($trimmed, FILTER_VALIDATE_IP) ? $trimmed : 'unknown');
}

function getUserAgent(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
}

function formatLogContext(array $context): string
{
    return $context
        |> (fn($ctx) => json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        |> (fn($json) => empty($context) ? '' : " | Context: $json");
}

function logError(string $message, array $context = []): void
{
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $context['ip'] = getClientIP();
    $context['user_agent'] = getUserAgent();
    
    $log_entry = $context
        |> formatLogContext(...)
        |> (fn($ctx) => "[$timestamp] ERROR: $message$ctx" . PHP_EOL);
    
    error_log($log_entry, 3, $log_file);
}

function logSecurity(string $event, array $context = []): void
{
    $log_file = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $user = $_SESSION['username'] ?? 'anonymous';
    
    $context['user_agent'] = getUserAgent();
    
    $log_entry = $context
        |> (fn($ctx) => json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        |> (fn($ctx_str) => !empty($context) ? " | $ctx_str" : '')
        |> (fn($ctx_str) => "[$timestamp] SECURITY: $event | User: $user | IP: $ip$ctx_str" . PHP_EOL);
    
    error_log($log_entry, 3, $log_file);
}

function logInfo(string $message, array $context = []): void
{
    $log_file = __DIR__ . '/../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = $context
        |> (fn($ctx) => json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        |> (fn($ctx_str) => !empty($context) ? " | $ctx_str" : '')
        |> (fn($ctx_str) => "[$timestamp] INFO: $message$ctx_str" . PHP_EOL);
    
    error_log($log_entry, 3, $log_file);
}

function logLogin(string $username, bool $success): void
{
    $status = $success ? 'SUCCESS' : 'FAILED';
    $ip = getClientIP();
    logSecurity("Login $status for user: $username from IP: $ip");
}

function logDatabaseError(string $query, string $error): void
{
    logError("Database error: $error", ['query' => $query]);
}
