<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Models\CategoryTaxonomy;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Arkenstone\Core\ECommerce\Contracts\CategoryTaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;

class CategoryTaxonomyController extends Controller
{

    public int $PER_PAGE;
    public string $ORDER;

    protected CategoryTaxonomyServiceInterface $categoryTaxonomyService;

    public function __construct(CategoryTaxonomyServiceInterface $categoryTaxonomyService)
    {
        $this->categoryTaxonomyService = $categoryTaxonomyService;
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }



    /**
     * Sync taxonomies for a category (API endpoint)
     */
    public function syncCategoryTaxonomies(Request $request, int $categoryId)
    {
        $request->validate([
            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'exists:taxonomies,id',
        ]);

        $category = Category::findOrFail($categoryId);

        $this->categoryTaxonomyService->syncForCategory($category, $request->taxonomy_ids);

        return ResponseProtocol::success('Category taxonomies synced successfully');
    }

    /**
     * Attach additional taxonomies without removing existing
     */
    public function attachTaxonomies(Request $request, int $categoryId)
    {
        $request->validate([
            'taxonomy_ids' => 'required|array',
            'taxonomy_ids.*' => 'exists:taxonomies,id',
        ]);

        $category = Category::findOrFail($categoryId);

        $this->categoryTaxonomyService->attachToCategory($category, $request->taxonomy_ids);

        return ResponseProtocol::success('Taxonomies attached successfully');
    }

    /**
     * Detach a taxonomy from a category
     */
    public function detachTaxonomy(int $categoryId, int $taxonomyId)
    {
        $category = Category::findOrFail($categoryId);
        $taxonomy = Taxonomy::findOrFail($taxonomyId);

        $this->categoryTaxonomyService->detachFromCategory($category, $taxonomy);

        return ResponseProtocol::success('Taxonomy detached successfully');
    }

    /**
     * Get all taxonomies for a category (optional type filter)
     */
    public function getCategoryTaxonomies(int $categoryId)
    {
        $category = Category::findOrFail($categoryId);

        $taxonomies = $this->categoryTaxonomyService->getCategoryTaxonomies($category);

        return ResponseProtocol::success($taxonomies, "Category taxonomies retrieved successfully");
    }

    /**
     * Get all categories for a taxonomy
     */
    public function getCategoriesByTaxonomy(Request $request, int $taxonomyId)
    {

        $request->validate([
            'with' => 'nullable|array',
        ]);

        $taxonomy = Taxonomy::findOrFail($taxonomyId);

        $categories = $this->categoryTaxonomyService->getCategoriesByTaxonomy($taxonomy, $request->with ?? []);

        return ResponseProtocol::success($categories, "Categories retrieved successfully");
    }
}