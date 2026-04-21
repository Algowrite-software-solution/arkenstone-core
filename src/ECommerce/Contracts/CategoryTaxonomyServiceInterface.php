<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Product\Models\Category;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;

interface CategoryTaxonomyServiceInterface
{
    public function attachToCategory(Category $category, array $taxonomyIds): void;
    public function syncForCategory(Category $category, array $taxonomyIds): void;
    public function detachFromCategory(Category $category, Taxonomy $taxonomy): void;

    public function getCategoryTaxonomies(Category $category);
    public function getCategoriesByTaxonomy(Taxonomy $taxonomy, array $with = []);
}