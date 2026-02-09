<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Arkenstone\Core\ECommerce\Product\Models\BundleItem;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class BundleService
{
    /**
     * Create a new bundle.
     */
    public function create(array $data): Bundle
    {
        return DB::transaction(function () use ($data) {
            $bundle = Bundle::create(['name' => $data['name']]);

            if (!empty($data['product_ids'])) {
                $this->addItems($bundle, $data['product_ids']);
            }

            return $bundle;
        });
    }

    /**
     * Update a bundle.
     */
    public function update(Bundle $bundle, array $data): Bundle
    {
        return DB::transaction(function () use ($bundle, $data) {
            if (isset($data['name'])) {
                $bundle->update(['name' => $data['name']]);
            }

            if (isset($data['product_ids'])) {
                // Sync items: delete existing and add new
                // Or just add/remove? 'product_ids' usually implies the final state in a sync operation.
                // Let's assume sync for simplicity unless user requested add/remove specific endpoints.
                $bundle->items()->delete();
                $this->addItems($bundle, $data['product_ids']);
            }

            return $bundle->refresh();
        });
    }

    /**
     * Add items to a bundle with recursion checking.
     */
    public function addItems(Bundle $bundle, array $productIds): void
    {
        // 1. Identify all "Parent Products" that use this Bundle.
        // A Bundle might be used by multiple products (reusable definition).
        $parentProducts = Product::where('bundle_id', $bundle->id)->get();

        foreach ($productIds as $productId) {
            $childProduct = Product::find($productId);
            if (!$childProduct)
                continue;

            // Check against each parent product
            foreach ($parentProducts as $parentProduct) {
                $this->validateRecursion($parentProduct, $childProduct);
            }

            // Also validation: A bundle cannot contain itself if we treat Bundle as a standalone entity,
            // but here Bundle is linked to Product. The recursion is via Products.

            // Create the item
            BundleItem::create([
                'bundle_id' => $bundle->id,
                'product_id' => $productId,
            ]);
        }
    }

    /**
     * Validate that adding $childProduct to a bundle used by $parentProduct 
     * does not create a cycle.
     * 
     * @throws Exception
     */
    protected function validateRecursion(Product $parentProduct, Product $childProduct): void
    {
        // 1. Direct Self-Reference: Parent cannot contain itself.
        if ($parentProduct->id === $childProduct->id) {
            throw new Exception("Recursion detected: Product '{$parentProduct->name}' cannot duplicate itself inside its own bundle.");
        }

        // 2. Transitive Recursion:
        // If Child is a bundle, does it contain Parent?
        if ($childProduct->isBundle()) {
            // $childProduct->bundle is the Bundle definition.
            // We need to check if that Bundle contains $parentProduct.
            if ($this->bundleContainsProduct($childProduct->bundle, $parentProduct->id)) {
                throw new Exception("Recursion detected: Product '{$childProduct->name}' already contains '{$parentProduct->name}'.");
            }
        }
    }

    /**
     * Check if a Bundle (and its sub-bundles) contains a specific product ID.
     */
    public function bundleContainsProduct(?Bundle $bundle, int $targetProductId): bool
    {
        if (!$bundle)
            return false;

        $bundle->load('items.product'); // Load immediate items

        foreach ($bundle->items as $item) {
            $itemProduct = $item->product;
            if (!$itemProduct)
                continue;

            // Check immediate match
            if ($itemProduct->id === $targetProductId) {
                return true;
            }

            // Recurse if the item is also a bundle
            if ($itemProduct->isBundle()) {
                if ($this->bundleContainsProduct($itemProduct->bundle, $targetProductId)) {
                    return true;
                }
            }
        }

        return false;
    }
}
