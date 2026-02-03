<?php

function getClientIP() {
    $ip = 'unknown';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
}

function logError($message, $context = []) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $context['ip'] = $ip;
    $context['user_agent'] = getUserAgent();
    $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $log_entry = "[$timestamp] ERROR: $message$context_str" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

function logSecurity($event, $context = []) {
    $log_file = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $user = $_SESSION['username'] ?? 'anonymous';
    $user_agent = getUserAgent();
    $context['user_agent'] = $user_agent;
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
