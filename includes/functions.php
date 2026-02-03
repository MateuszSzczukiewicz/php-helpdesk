<?php

declare(strict_types=1);

require_once __DIR__ . '/TicketStatus.php';
require_once __DIR__ . '/UserRole.php';

function getStatusLabel(string|TicketStatus $status): string
{
    return match (true) {
        $status instanceof TicketStatus => $status->getLabel(),
        default => $status 
            |> TicketStatus::tryFromString(...)
            |> (fn($s) => $s?->getLabel() ?? htmlspecialchars($status))
    };
}

function getStatusBadgeColor(string|TicketStatus $status): string
{
    return match (true) {
        $status instanceof TicketStatus => $status->getColor(),
        default => $status
            |> TicketStatus::tryFromString(...)
            |> (fn($s) => $s?->getColor() ?? '#333')
    };
}

function formatDate(string $datetime): string
{
    return $datetime
        |> strtotime(...)
        |> (fn($timestamp) => date("Y-m-d H:i", $timestamp));
}

function redirect(string $url): never
{
    header("Location: $url");
    exit();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return match (true) {
        !isset($_SESSION['role']) => false,
        default => $_SESSION['role']
            |> UserRole::tryFromString(...)
            |> (fn($role) => $role?->isAdmin() ?? false)
    };
}

function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ];
}
