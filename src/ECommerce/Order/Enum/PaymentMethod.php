<?php

namespace Arkenstone\Core\ECommerce\Order\Enum;

enum PaymentMethod: string
{
    case CARD = 'card';
    case COD = 'cod';
    case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::CARD => 'Card Payment',
            self::COD => 'Cash on Delivery',
            self::BANK_TRANSFER => 'Bank Transfer',
        };
    }

    public function requiresGateway(): bool
    {
        return $this === self::CARD;
    }

    public function requiresManualConfirmation(): bool
    {
        return $this === self::BANK_TRANSFER;
    }

    public function isPayOnDelivery(): bool
    {
        return $this === self::COD;
    }
}
