<?php

function getStatusLabel($status) {
    switch ($status) {
        case 'new':
            return '<span class="status-new">New</span>';
        case 'in_progress':
            return '<span class="status-in_progress">In Progress</span>';
        case 'resolved':
            return '<span class="status-resolved">Resolved</span>';
        case 'cancelled':
            return '<span style="color:gray">Cancelled</span>';
        default:
            return htmlspecialchars($status);
    }
}

function getStatusBadgeColor($status) {
    switch ($status) {
        case 'new':
            return '#007bff';
        case 'in_progress':
            return '#ffc107';
        case 'resolved':
            return '#28a745';
        case 'cancelled':
            return '#6c757d';
        default:
            return '#333';
    }
}

function formatDate($datetime) {
    return date("Y-m-d H:i", strtotime($datetime));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ];
}
