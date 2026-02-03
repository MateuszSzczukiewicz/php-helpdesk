<?php

function logError($message, $context = []) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $log_entry = "[$timestamp] ERROR: $message$context_str" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

function logSecurity($event, $context = []) {
    $log_file = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['username'] ?? 'anonymous';
    $context_str = !empty($context) ? ' | ' . json_encode($context) : '';
    $log_entry = "[$timestamp] SECURITY: $event | User: $user | IP: $ip$context_str" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

function logInfo($message, $context = []) {
    $log_file = __DIR__ . '/../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? ' | ' . json_encode($context) : '';
    $log_entry = "[$timestamp] INFO: $message$context_str" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

function logLogin($username, $success) {
    $status = $success ? 'SUCCESS' : 'FAILED';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    logSecurity("Login $status for user: $username from IP: $ip");
}

function logDatabaseError($query, $error) {
    logError("Database error: $error", ['query' => $query]);
}
