<?php
/**
 * Logging System
 * 
 * Provides logging functionality for errors and security events
 */

/**
 * Log an error message
 * 
 * @param string $message The error message
 * @param array $context Additional context data
 * @return void
 */
function logError($message, $context = []) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $log_entry = "[$timestamp] ERROR: $message$context_str" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

/**
 * Log a security event
 * 
 * @param string $event The security event description
 * @param array $context Additional context data
 * @return void
 */
function logSecurity($event, $context = []) {
    $log_file = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['username'] ?? 'anonymous';
    $context_str = !empty($context) ? ' | ' . json_encode($context) : '';
    $log_entry = "[$timestamp] SECURITY: $event | User: $user | IP: $ip$context_str" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

/**
 * Log general information
 * 
 * @param string $message The info message
 * @param array $context Additional context data
 * @return void
 */
function logInfo($message, $context = []) {
    $log_file = __DIR__ . '/../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? ' | ' . json_encode($context) : '';
    $log_entry = "[$timestamp] INFO: $message$context_str" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

/**
 * Log login attempts
 * 
 * @param string $username The username attempting login
 * @param bool $success Whether login was successful
 * @return void
 */
function logLogin($username, $success) {
    $status = $success ? 'SUCCESS' : 'FAILED';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    logSecurity("Login $status for user: $username from IP: $ip");
}

/**
 * Log database errors
 * 
 * @param string $query The query that failed
 * @param string $error The error message
 * @return void
 */
function logDatabaseError($query, $error) {
    logError("Database error: $error", ['query' => $query]);
}
