<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;

interface ProductTaxonomyServiceInterface
{
   public function attachToProduct(Product $product, array $productTaxonomyData): void;
    public function syncForProduct(Product $product, array $productTaxonomyData): void;
    public function detachFromProduct(Product $product, Taxonomy $taxonomy): void;

    public function getProductTaxonomies(Product $product, ?int $typeId = null);
    public function getProductsByTaxonomy(Taxonomy $taxonomy, array $with = []);
}
