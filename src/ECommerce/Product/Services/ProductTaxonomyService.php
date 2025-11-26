<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\ProductTaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;


class ProductTaxonomyService implements ProductTaxonomyServiceInterface
{
    public function getName(): string
    {
        return "Product Taxonomy Service";
    }

     public function attachToProduct(Product $product, array $taxonomyIds): void
    {
        // Attach an array of taxonomy IDs to the product
        $product->taxonomies()->attach($taxonomyIds);
    }

    public function syncForProduct(Product $product, array $taxonomyIds): void
    {
        // Sync ensures only the provided IDs are attached, detaching any others.
        $product->taxonomies()->sync($taxonomyIds);
    }

    // Detach a specific taxonomy from a product
    public function detachFromProduct(Product $product, Taxonomy $taxonomy): void
    {
        // Detach a single taxonomy ID
        $product->taxonomies()->detach($taxonomy->id);
    }

    public function getProductsByTaxonomy(Taxonomy $taxonomy, array $with = [])
    {
        return $taxonomy->products()->with($with)->paginate(15);
    }


    public function getProductTaxonomies(Product $product, ?int $typeId = null)
    {
        $rel = $product->taxonomies()->with('type');
        if ($typeId)
            $rel->where('taxonomy_type_id', $typeId);
        return $rel->get();
    }
}
