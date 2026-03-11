<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\BundleServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Arkenstone\Core\ECommerce\Product\Models\BundleItem;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class BundleService implements BundleServiceInterface
{

    public function getName(): string
    {
        return "Bundle Service";
    }

    public function getAll()
    {
        return Bundle::with('items.product')->get();
    }


    public function get(int $id)
    {
        return Bundle::with('items.product')->findOrFail($id);
    }

    /**
     * Create a new bundle.
     */
    public function create(array $data): Bundle
    {
        return DB::transaction(function () use ($data) {
            $bundle = Bundle::create(['name' => $data['name']]);

            if (!empty($data['product_ids'])) {
                $this->addItems($bundle->id, $data['product_ids']);
            }

            return $bundle->refresh()->load('items.product');
        });
    }

    /**
     * Update a bundle.
     */
    public function update(int $id, array $data): Bundle
    {
        return DB::transaction(function () use ($id, $data) {
            $bundle = Bundle::findOrFail($id);
            if (isset($data['name'])) {
                $bundle->update(['name' => $data['name']]);
            }

            if (isset($data['product_ids'])) {
                $bundle->items()->delete();
                $this->addItems($bundle->id, $data['product_ids']);
            }

            return $bundle->refresh()->load('items.product');
        });
    }

    public function delete(int $id): void
    {
        $bundle = Bundle::findOrFail($id);
        $bundle->delete();
    }

    /**
     * Add items to a bundle. 
     * Ensures that no bundle is added as a child (depth-1 only).
     */
    public function addItems(int $bundleId, array $productIds): void
    {
        foreach (array_unique($productIds) as $productId) {
            $childProduct = Product::find($productId);
            if (!$childProduct) {
                continue;
            }

            if ($childProduct->isBundle()) {
                if ($childProduct->bundle_id === $bundleId) {
                    // "a bundle can never have itself as the child"
                    throw new Exception("A bundle can never have itself as a child.");
                }
                // "a bundle can never be a child of another bundle"
                throw new Exception("A bundle can never be a child of another bundle.");
            }

            BundleItem::create([
                'bundle_id' => $bundleId,
                'product_id' => $productId,
            ]);
        }
    }
}
