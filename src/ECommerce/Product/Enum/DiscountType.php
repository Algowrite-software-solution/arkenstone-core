<?php

namespace Arkenstone\Core\ECommerce\Product\Enum;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
}