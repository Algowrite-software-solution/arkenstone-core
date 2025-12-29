<?php

namespace Arkenstone\Core\ECommerce\Order\Enum;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage',
            self::FIXED => 'Fixed Amount',
            self::NONE => 'No Discount',
        };
    }

    public function calculate(float $price, float $value): float
    {
        return match ($this) {
            self::PERCENTAGE => $price * ($value / 100),
            self::FIXED => min($value, $price),
            self::NONE => 0,
        };
    }
}
