<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\CategoryTaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;


class CategoryTaxonomyService implements CategoryTaxonomyServiceInterface
{
    public function getName(): string
    {
        return "Category Taxonomy Service";
    }

    /**
     * Attach multiple taxonomies to a category without removing existing
     */
    public function attachToCategory(Category $category, array $taxonomyIds): void
    {
        $category->taxonomies()->syncWithoutDetaching($taxonomyIds);
    }

    /**
     * Sync taxonomies for a category (remove old, add new)
     */
    public function syncForCategory(Category $category, array $taxonomyIds): void
    {
        $category->taxonomies()->sync($taxonomyIds);
    }

    /**
     * Detach a specific taxonomy from a category
     */
    public function detachFromCategory(Category $category, Taxonomy $taxonomy): void
    {
        $category->taxonomies()->detach($taxonomy->id);
    }

    /**
     * Get taxonomies for a category, optionally filter by type (pivot field)
     */
    public function getCategoryTaxonomies(Category $category)
    {
        $query = $category->taxonomies();
        return $query->get();
    }

    /**
     * Get categories for a taxonomy
     */
    public function getCategoriesByTaxonomy(Taxonomy $taxonomy, array $with = [])
    {
        return $taxonomy->categories()->with($with)->get();
    }
}