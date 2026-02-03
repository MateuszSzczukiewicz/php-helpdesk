<?php

declare(strict_types=1);

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function canManageTickets(): bool
    {
        return $this === self::ADMIN;
    }

    public function canManageCategories(): bool
    {
        return $this === self::ADMIN;
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
