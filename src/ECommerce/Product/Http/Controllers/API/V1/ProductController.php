<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\ProductServiceInterface;
use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\ProductResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\ProductCollection;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{

    public function __construct(private ProductServiceInterface $productService)
    {
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'min_price', 'max_price', 'brand_id', 'category_ids', 'categories', 'is_active', 'per_page', 'brand', 'order_by', 'order', 'with']);

        // Convert category_ids string to array if needed
        if (!empty($filters['category_ids']) && !is_array($filters['category_ids'])) {
            $filters['categories'] = explode(',', $filters['category_ids']);
        } elseif (!empty($filters['category_ids'])) {
            $filters['categories'] = $filters['category_ids'];
        }

        $products = $this->productService->search($filters);

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

        $product = $this->productService->create($validated);

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
        $product = $this->productService->find($id, ['brand', 'categories', 'taxonomies', 'images', 'primaryImage']);

        if (!$product) {
            return ResponseProtocol::failed(
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
            return ResponseProtocol::failed(
                null,
                'Product not found',
                404
            );
        }

        $validated = $request->validated();
        $product = $this->productService->update($product, $validated);

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
            return ResponseProtocol::failed(
                null,
                'Product not found',
                404
            );
        }

        $this->productService->delete($product);

        return ResponseProtocol::success(
            null,
            'Product deleted successfully'
        );
    }
}
