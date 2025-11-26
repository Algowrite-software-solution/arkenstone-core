<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\CategoryServiceInterface;
use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreCategoryRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateCategoryRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\CategoryResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\CategoryCollection;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Arkenstone\Core\ECommerce\Product\Services\CategoryService;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{

    public function __construct(private CategoryServiceInterface $categoryService)
    {
      
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $isActive = $request->input('is_active');
        $rootOnly = $request->input('root_only', false);

        $query = Category::query()->with(['parent', 'children']);

        if ($isActive !== null) {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        if (filter_var($rootOnly, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('parent_id');
        }

        $categories = $query->paginate($perPage);

        return ResponseProtocol::success(
            new CategoryCollection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $category = $this->categoryService->createCategory($validated);

        $category->load(['parent', 'children']);

        return ResponseProtocol::success(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Display the specified category.
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::with(['parent', 'children'])->find($id);

        if (!$category) {
            return ResponseProtocol::failed(
                null,
                'Category not found',
                404
            );
        }

        return ResponseProtocol::success(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $success = $this->categoryService->updateCategory($id, $validated);

        if (!$success) {
            return ResponseProtocol::failed(
                null,
                'Category not found',
                404
            );
        }

        $category = Category::with(['parent', 'children'])->find($id);

        return ResponseProtocol::success(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    /**
     * Remove the specified category.
     */
    public function destroy(int $id): JsonResponse
    {
        $success = $this->categoryService->deleteCategory($id);

        if (!$success) {
            return ResponseProtocol::failed(
                null,
                'Category not found',
                404
            );
        }

        return ResponseProtocol::success(
            null,
            'Category deleted successfully'
        );
    }

    /**
     * Get children of a specific category.
     */
    public function children(int $id): JsonResponse
    {
        $children = $this->categoryService->getCategoryChildren($id);

        return ResponseProtocol::success(
            CategoryResource::collection($children),
            'Category children retrieved successfully'
        );
    }

    /**
     * Get root categories (no parent).
     */
    public function roots(): JsonResponse
    {
        $rootCategories = $this->categoryService->getRootCategories();

        return ResponseProtocol::success(
            CategoryResource::collection($rootCategories),
            'Root categories retrieved successfully'
        );
    }
}
