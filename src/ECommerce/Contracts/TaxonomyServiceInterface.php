<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaxonomyServiceInterface
{
    // Taxonomies
    public function listTaxonomies(array $filters = []): LengthAwarePaginator;
    public function createTaxonomy(array $data): Taxonomy;
    public function updateTaxonomy(Taxonomy $taxonomy, array $data): Taxonomy;
    public function deleteTaxonomy(Taxonomy $taxonomy): bool;
    public function getActiveTaxonomies();
}
