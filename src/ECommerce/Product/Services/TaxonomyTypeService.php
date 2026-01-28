<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\TaxonomyTypeServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Illuminate\Pagination\LengthAwarePaginator;

class TaxonomyTypeService implements TaxonomyTypeServiceInterface
{
    protected int $PER_PAGE = APIDefaults::PER_PAGE;
    protected string $ORDER = APIDefaults::ORDER;

    public function listTypes(array $filters = []): LengthAwarePaginator
    {
        $q = TaxonomyType::query();

        // By default, we include taxonomies.
        // We only skip loading them if 'only_taxonomy_type' is explicitly true.
        if (empty($filters['only_taxonomy_type'])) {
            $q->with('taxonomies');
        }

        // --- Apply other existing filters ---

        // Filter by the name of the Taxonomy Type
        if (!empty($filters['search'])) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by a specific Taxonomy Type ID
        if (isset($filters['taxonomy_type_id'])) {
            $q->where('id', $filters['taxonomy_type_id']);
        }

        // Filter Taxonomy Types that have a Taxonomy with a specific name
        if (!empty($filters['taxonomy_name'])) {
            $q->whereHas('taxonomies', function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['taxonomy_name'] . '%');
            });
        }

        return $q->orderBy('created_at', $filters['order'] ?? $this->ORDER)->paginate($filters['per_page'] ?? $this->PER_PAGE);
    }

    public function createType(array $data): TaxonomyType
    {
        return TaxonomyType::create($data);
    }

    public function updateType(TaxonomyType $type, array $data): TaxonomyType
    {
        $type->update($data);
        return $type->fresh();
    }

    public function deleteType(TaxonomyType $type): bool
    {
        return $type->delete();
    }
}