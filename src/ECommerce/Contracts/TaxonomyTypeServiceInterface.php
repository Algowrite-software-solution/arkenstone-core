<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaxonomyTypeServiceInterface
{
    // Types of Taxonomies
    public function listTypes(array $filters = []): LengthAwarePaginator;
    public function createType(array $data): TaxonomyType;
    public function updateType(TaxonomyType $type, array $data): TaxonomyType;
    public function deleteType(TaxonomyType $type): bool;
}