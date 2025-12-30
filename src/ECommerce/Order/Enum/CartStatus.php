<?php

namespace Arkenstone\Core\ECommerce\Order\Enum;

enum CartStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case MIGRATED = 'migrated';
    case EXPIRED = 'expired';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::MIGRATED => 'Migrated',
            self::EXPIRED => 'Expired',
            self::ABANDONED => 'Abandoned',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}
