<?php

namespace Arkenstone\Core\ECommerce\Order\Enum;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COD_PENDING = 'cod_pending';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAYMENT_INITIATED = 'payment_initiated';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDING = 'refunding';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COD_PENDING => 'COD Pending',
            self::AWAITING_PAYMENT => 'Awaiting Payment',
            self::PAYMENT_INITIATED => 'Payment Initiated',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::REFUNDING => 'Refunding',
            self::REFUNDED => 'Refunded',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [
            self::PAID,
            self::COD_PENDING,
        ]);
    }

    public function isFailed(): bool
    {
        return in_array($this, [
            self::FAILED,
            self::CANCELLED,
        ]);
    }

    public function isPending(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::AWAITING_PAYMENT,
            self::PAYMENT_INITIATED,
        ]);
    }
}
