<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\ProductTaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductTaxonomy;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Illuminate\Database\Eloquent\Collection;

class ProductTaxonomyService implements ProductTaxonomyServiceInterface
{
    public function getName(): string
    {
        return "Product Taxonomy Service";
    }

    public function attachTaxonomy(int $productId, int $taxonomyId): bool
    {
        $product = Product::find($productId);
        $taxonomy = Taxonomy::find($taxonomyId);

        if (!$product || !$taxonomy) {
            return false;
        }

        // Check if already attached
        $exists = ProductTaxonomy::where('product_id', $productId)
            ->where('taxonomy_id', $taxonomyId)
            ->exists();

        if ($exists) {
            return true;
        }

        ProductTaxonomy::create([
            'product_id' => $productId,
            'taxonomy_id' => $taxonomyId,
        ]);

        return true;
    }

    public function detachTaxonomy(int $productId, int $taxonomyId): bool
    {
        return ProductTaxonomy::where('product_id', $productId)
            ->where('taxonomy_id', $taxonomyId)
            ->delete() > 0;
    }

    public function syncTaxonomies(int $productId, array $taxonomyIds): bool
    {
        $product = Product::find($productId);

        if (!$product) {
            return false;
        }

        $product->taxonomies()->sync($taxonomyIds);

        return true;
    }

    public function getTaxonomiesByProduct(int $productId): Collection
    {
        $product = Product::find($productId);

        if (!$product) {
            return new Collection();
        }

        return $product->taxonomies;
    }

    public function getProductsByTaxonomy(int $taxonomyId): Collection
    {
        $taxonomy = Taxonomy::find($taxonomyId);

        if (!$taxonomy) {
            return new Collection();
        }

        return $taxonomy->products;
    }
}
