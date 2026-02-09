<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Arkenstone\Core\ECommerce\Product\Models\BundleItem;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class BundleItemFactory extends Factory
{
    protected $model = BundleItem::class;

    public function definition(): array
    {
        return [
            'bundle_id' => Bundle::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
