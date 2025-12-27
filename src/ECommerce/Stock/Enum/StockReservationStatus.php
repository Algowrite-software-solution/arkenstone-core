<?php

namespace Arkenstone\Core\ECommerce\Stock\Enum;

enum StockReservationStatus: string
{
    case PENDING = 'pending';
    case CHECKING_OUT = 'checking_out';
    case COMMITTED = 'committed';
    case FULFILLED = 'fulfilled';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case RELEASED = 'released';

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get validation rule string
     */
    public static function validationRule(): string
    {
        return 'in:' . implode(',', self::values());
    }

    /**
     * Get active statuses for filtering
     */
    public static function activeStatuses(): array
    {
        return [
            self::PENDING->value,
            self::CHECKING_OUT->value,
            self::COMMITTED->value,
        ];
    }

    /**
     * Get statuses that are considered expired
     */
    public static function expiredStatuses(): array
    {
        return [
            self::PENDING->value,
            self::CHECKING_OUT->value,
        ];
    }

    /**
     * Check if status can transition to committed
     */
    public function canCommit(): bool
    {
        return in_array($this, [self::PENDING, self::CHECKING_OUT]);
    }

    /**
     * Check if status can be fulfilled
     */
    public function canFulfill(): bool
    {
        return $this === self::COMMITTED;
    }

    /**
     * Check if status is final (cannot be changed)
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::FULFILLED, self::CANCELLED, self::EXPIRED]);
    }
}
