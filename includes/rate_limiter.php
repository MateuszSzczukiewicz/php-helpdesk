<?php

function getRateLimitKey($identifier, $action) {
    return "rate_limit_{$action}_" . md5($identifier);
}

function checkRateLimit($identifier, $action, $maxAttempts = 5, $timeWindow = 900) {
    $key = getRateLimitKey($identifier, $action);
    $logFile = __DIR__ . '/../logs/rate_limit.log';
    $attempts = [];
    
    if (file_exists($logFile)) {
        $data = file_get_contents($logFile);
        $allAttempts = json_decode($data, true) ?: [];
        $attempts = $allAttempts[$key] ?? [];
    }
    
    $currentTime = time();
    $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
        return ($currentTime - $timestamp) < $timeWindow;
    });
    
    if (count($attempts) >= $maxAttempts) {
        $oldestAttempt = min($attempts);
        $timeRemaining = $timeWindow - ($currentTime - $oldestAttempt);
        return [
            'allowed' => false,
            'remaining' => 0,
            'retry_after' => $timeRemaining
        ];
    }
    
    return [
        'allowed' => true,
        'remaining' => $maxAttempts - count($attempts) - 1,
        'retry_after' => 0
    ];
}

function recordRateLimitAttempt($identifier, $action) {
    $key = getRateLimitKey($identifier, $action);
    $logFile = __DIR__ . '/../logs/rate_limit.log';
    
    $allAttempts = [];
    if (file_exists($logFile)) {
        $data = file_get_contents($logFile);
        $allAttempts = json_decode($data, true) ?: [];
    }
    
    if (!isset($allAttempts[$key])) {
        $allAttempts[$key] = [];
    }
    
    $allAttempts[$key][] = time();
    
    file_put_contents($logFile, json_encode($allAttempts));
}

function clearRateLimit($identifier, $action) {
    $key = getRateLimitKey($identifier, $action);
    $logFile = __DIR__ . '/../logs/rate_limit.log';
    
    if (file_exists($logFile)) {
        $data = file_get_contents($logFile);
        $allAttempts = json_decode($data, true) ?: [];
        
        if (isset($allAttempts[$key])) {
            unset($allAttempts[$key]);
            file_put_contents($logFile, json_encode($allAttempts));
        }
    }
}
