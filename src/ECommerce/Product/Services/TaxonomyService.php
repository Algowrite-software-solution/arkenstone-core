<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\TaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Illuminate\Database\Eloquent\Collection;

class TaxonomyService implements TaxonomyServiceInterface
{
    public function getName(): string
    {
        return "Taxonomy Service";
    }

    public function getAllTaxonomies(): Collection
    {
        return Taxonomy::all();
    }

    public function getTaxonomyById(int $id): ?Taxonomy
    {
        return Taxonomy::find($id);
    }

    public function createTaxonomy(array $data): Taxonomy
    {
        return Taxonomy::create($data);
    }

    public function updateTaxonomy(int $id, array $data): bool
    {
        $taxonomy = Taxonomy::find($id);

        if (!$taxonomy) {
            return false;
        }

        return $taxonomy->update($data);
    }

    public function deleteTaxonomy(int $id): bool
    {
        $taxonomy = Taxonomy::find($id);

        if (!$taxonomy) {
            return false;
        }

        return $taxonomy->delete();
    }

    public function getActiveTaxonomies(): Collection
    {
        return Taxonomy::where('is_active', true)->get();
    }

    public function getTaxonomiesByType(int $typeId): Collection
    {
        return Taxonomy::where('taxonomy_type_id', $typeId)->get();
    }
}
