<?php

namespace Arkenstone\Core\Database\Seeders;

use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Arkenstone\Core\ECommerce\Product\Models\BundleItem;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Database\Seeder;

class BundleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create some regular products if they don't exist
        $products = Product::limit(10)->get();
        if ($products->count() < 5) {
            $products = Product::factory()->count(10)->create();
        }

        // 2. Create Bundles
        $bundles = Bundle::factory()->count(3)->create();

        // 3. Add products to bundles
        foreach ($bundles as $bundle) {
            $randomProducts = $products->random(rand(2, 4));
            foreach ($randomProducts as $product) {
                BundleItem::create([
                    'bundle_id' => $bundle->id,
                    'product_id' => $product->id,
                ]);
            }

            // 4. Link a product to this bundle (The product that *is* the bundle)
            Product::factory()->create([
                'name' => $bundle->name . ' Product',
                'bundle_id' => $bundle->id,
            ]);
        }
    }
}
