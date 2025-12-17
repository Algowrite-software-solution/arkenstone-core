<?php

namespace Arkenstone\Core\ECommerce\Stock\Enum;

enum ReferenceType: string
{
    case CART = 'cart';
    case ORDER = 'order';
    case QUOTE = 'quote';

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
}
