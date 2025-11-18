<?php

namespace Arkenstone\Core\ECommerce\Contracts;

interface ProductTaxonomyServiceInterface
{
    public function attachTaxonomy(int $productId, int $taxonomyId);
    public function detachTaxonomy(int $productId, int $taxonomyId);
    public function syncTaxonomies(int $productId, array $taxonomyIds);
    public function getTaxonomiesByProduct(int $productId);
    public function getProductsByTaxonomy(int $taxonomyId);
}
