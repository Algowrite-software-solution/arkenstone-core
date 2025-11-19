<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\TaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaxonomyService implements TaxonomyServiceInterface
{
    //Taxonomies
    public function listTaxonomies(array $filters = []): LengthAwarePaginator
    {
        $q = Taxonomy::query()->with(['type', 'parent', 'children']);
        if (isset($filters['taxonomy_type_id'])) {
            $q->where('taxonomy_type_id', $filters['taxonomy_type_id']);
        }
        if (isset($filters['type_slug'])) {
            $q->whereHas('type', fn($qq) => $qq->where('slug', $filters['type_slug']));
        }
        if (isset($filters['parent_id'])) {
        }
        return $q->paginate($filters['per_page'] ?? 15);
    }

    public function createTaxonomy(array $data): Taxonomy
    {
        return DB::transaction(function () use ($data) {
            return Taxonomy::create($data);
        });
    }

    public function updateTaxonomy(Taxonomy $taxonomy, array $data): Taxonomy
    {
        $taxonomy->update($data);
        return $taxonomy->fresh();
    }

    public function deleteTaxonomy(Taxonomy $taxonomy): bool
    {
        $taxonomy->delete();
        return true;
    }

    public function getActiveTaxonomies()
    {
        return Taxonomy::where('is_active', true)->get();
    }
}
