<?php

namespace Arkenstone\Core\ECommerce\Order\Enum;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Pending Payment',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::RETURNED => 'Returned',
        };
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING_PAYMENT => in_array($newStatus, [
                self::CONFIRMED,
                self::CANCELLED,
            ]),
            self::CONFIRMED => in_array($newStatus, [
                self::PROCESSING,
                self::CANCELLED,
            ]),
            self::PROCESSING => in_array($newStatus, [
                self::SHIPPED,
                self::CANCELLED,
            ]),
            self::SHIPPED => in_array($newStatus, [
                self::DELIVERED,
            ]),
            self::DELIVERED => in_array($newStatus, [
                self::COMPLETED,
                self::RETURNED,
            ]),
            self::COMPLETED => in_array($newStatus, [
                self::RETURNED,
            ]),
            default => false,
        };
    }

    public function isCancellable(): bool
    {
        return in_array($this, [
            self::PENDING_PAYMENT,
            self::CONFIRMED,
            self::PROCESSING,
        ]);
    }
}
