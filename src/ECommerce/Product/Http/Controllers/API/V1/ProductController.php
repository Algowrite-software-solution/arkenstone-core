<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\ProductResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\ProductCollection;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Services\ProductService;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['name', 'min_price', 'max_price', 'brand_id', 'category_ids', 'is_active']);

        $query = Product::query()
            ->with(['brand', 'categories', 'primaryImage']);

        // Apply filters
        if (!empty($filters['name'])) {
            $query->filterByName($filters['name']);
        }

        if (!empty($filters['min_price'])) {
            $query->minPrice($filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->maxPrice($filters['max_price']);
        }

        if (!empty($filters['brand_id'])) {
            $query->byBrand($filters['brand_id']);
        }

        if (!empty($filters['category_ids'])) {
            $categoryIds = is_array($filters['category_ids'])
                ? $filters['category_ids']
                : explode(',', $filters['category_ids']);
            $query->byCategories($categoryIds);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        $products = $query->paginate($perPage);

        return ResponseProtocol::success(
            new ProductCollection($products),
            'Products retrieved successfully'
        );
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::create($validated);

        // Attach categories if provided
        if (!empty($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        // Attach taxonomies if provided
        if (!empty($validated['taxonomy_ids'])) {
            $product->taxonomies()->sync($validated['taxonomy_ids']);
        }

        $product->load(['brand', 'categories', 'taxonomies', 'images']);

        return ResponseProtocol::success(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['brand', 'categories', 'taxonomies', 'images', 'primaryImage'])
            ->find($id);

        if (!$product) {
            return ResponseProtocol::error(
                null,
                'Product not found',
                404
            );
        }

        return ResponseProtocol::success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return ResponseProtocol::error(
                null,
                'Product not found',
                404
            );
        }

        $validated = $request->validated();

        $product->update($validated);

        // Update categories if provided
        if (isset($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        // Update taxonomies if provided
        if (isset($validated['taxonomy_ids'])) {
            $product->taxonomies()->sync($validated['taxonomy_ids']);
        }

        $product->load(['brand', 'categories', 'taxonomies', 'images']);

        return ResponseProtocol::success(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return ResponseProtocol::error(
                null,
                'Product not found',
                404
            );
        }

        $product->delete();

        return ResponseProtocol::success(
            null,
            'Product deleted successfully'
        );
    }
}
