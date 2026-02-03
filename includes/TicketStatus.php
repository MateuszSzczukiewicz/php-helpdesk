<?php

declare(strict_types=1);

enum TicketStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => '<span class="status-new">New</span>',
            self::IN_PROGRESS => '<span class="status-in_progress">In Progress</span>',
            self::RESOLVED => '<span class="status-resolved">Resolved</span>',
            self::CANCELLED => '<span style="color:gray">Cancelled</span>',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NEW => '#007bff',
            self::IN_PROGRESS => '#ffc107',
            self::RESOLVED => '#28a745',
            self::CANCELLED => '#6c757d',
        };
    }

    public function canBeChangedByUser(): bool
    {
        return $this === self::NEW;
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
