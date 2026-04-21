<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\TaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxonomyService implements TaxonomyServiceInterface
{

    protected int $PER_PAGE;
    protected string $ORDER;

    public function __construct()
    {
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }

    //Taxonomies
    public function listTaxonomies(array $filters = []): LengthAwarePaginator
    {
        $q = Taxonomy::query()->with(['type', 'parent', 'children']);

        $q->when(
            !($filters['with_inactive'] ?? false),
        fn($q) => $q->where('is_active', true)
        );


        if (isset($filters['taxonomy_type_id'])) {
            $q->where('taxonomy_type_id', $filters['taxonomy_type_id']);
        }
        if (isset($filters['type_slug'])) {
            $q->whereHas('type', fn($qq) => $qq->where('slug', $filters['type_slug']));
        }
        if (isset($filters['parent_id'])) {
            $q->where('parent_id', $filters['parent_id']);
        }
        if (!empty($filters['root_only'])) {
            $q->whereNull('parent_id');
        }
        if (!empty($filters['search'])) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $q->paginate($filters['per_page'] ?? $this->PER_PAGE);
    }

    public function createTaxonomy(array $data): Taxonomy
    {
        $data['slug'] = isset($data["slug"]) && !empty($data["slug"]) ? $data["slug"] : (isset($data["name"]) && !empty($data["name"]) ?Str::slug($data["name"]) : null);
        return DB::transaction(function () use ($data) {
            return Taxonomy::create($data);
        });
    }

    public function updateTaxonomy(Taxonomy $taxonomy, array $data): Taxonomy
    {
        if (isset($data["slug"]) && !empty($data["slug"]) || (isset($data["name"]) && !empty($data["name"]))) {
            $data['slug'] = isset($data["slug"]) && !empty($data["slug"]) ? $data["slug"] : (isset($data["name"]) && !empty($data["name"]) ?Str::slug($data["name"]) : null);
        }

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