<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\ProductImageServiceInterface;
use Arkenstone\Core\ECommerce\Contracts\ProductServiceInterface;
use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreProductImageRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateProductImageRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UploadProductImagesRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\ProductImageResource;
use Arkenstone\Core\ECommerce\Product\Services\ProductImageService;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductImageController extends Controller
{
    public function __construct(
        private ProductImageServiceInterface $productImageService,
        private ProductServiceInterface $productService
    ) {
    }

    /**
     * Display images for a specific product.
     */
    public function index(int $productId): JsonResponse
    {
        $images = $this->productImageService->getImagesByProductId($productId);

        return ResponseProtocol::success(
            ProductImageResource::collection($images),
            'Product images retrieved successfully'
        );
    }

    /**
     * Store a newly created product image.
     */
    public function store(StoreProductImageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $image = $this->productImageService->createImage($validated);

        return ResponseProtocol::success(
            new ProductImageResource($image),
            'Product image created successfully',
            201
        );
    }

    /**
     * Display the specified product image.
     */
    public function show(int $id): JsonResponse
    {
        $image = $this->productImageService->getImageById($id);

        if (!$image) {
            return ResponseProtocol::failed(
                null,
                'Product image not found',
                404
            );
        }

        return ResponseProtocol::success(
            new ProductImageResource($image),
            'Product image retrieved successfully'
        );
    }

    /**
     * Update the specified product image.
     */
    public function update(UpdateProductImageRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $success = $this->productImageService->updateImage($id, $validated);

        if (!$success) {
            return ResponseProtocol::failed(
                null,
                'Product image not found',
                404
            );
        }

        $image = $this->productImageService->getImageById($id);

        return ResponseProtocol::success(
            new ProductImageResource($image),
            'Product image updated successfully'
        );
    }

    /**
     * Remove the specified product image.
     */
    public function destroy(int $id): JsonResponse
    {
        $success = $this->productImageService->deleteImage($id);

        if (!$success) {
            return ResponseProtocol::failed(
                null,
                'Product image not found',
                404
            );
        }

        return ResponseProtocol::success(
            null,
            'Product image deleted successfully'
        );
    }

    /**
     * Set an image as primary for a product.
     */
    public function setPrimary(int $productId, int $imageId): JsonResponse
    {
        $success = $this->productImageService->setPrimaryImage($productId, $imageId);

        if (!$success) {
            return ResponseProtocol::failed(
                null,
                'Product image not found or does not belong to this product',
                404
            );
        }

        $image = $this->productImageService->getImageById($imageId);

        return ResponseProtocol::success(
            new ProductImageResource($image),
            'Primary image set successfully'
        );
    }

    /**
     * Get the primary image for a product.
     */
    public function getPrimary(int $productId): JsonResponse
    {
        $image = $this->productImageService->getPrimaryImage($productId);

        if (!$image) {
            return ResponseProtocol::failed(
                null,
                'No primary image found for this product',
                404
            );
        }

        return ResponseProtocol::success(
            new ProductImageResource($image),
            'Primary image retrieved successfully'
        );
    }

    /**
     * Upload multiple images for a product.
     */
    public function upload(UploadProductImagesRequest $request, int $productId): JsonResponse
    {
        // Verify product exists
        $product = $this->productService->find($productId);
        if (!$product) {
            return ResponseProtocol::failed(
                null,
                'Product not found',
                404
            );
        }

        $validated = $request->validated();

        // Prepare metadata for images
        $metadata = [
            'alt_texts' => $validated['alt_texts'] ?? [],
            'sort_orders' => $validated['sort_orders'] ?? [],
            'primary_index' => $validated['primary_index'] ?? null,
        ];

        // Upload images using ProductService
        $uploadedImages = $this->productService->addImages(
            $validated['images'],
            $productId,
            $metadata
        );

        if ($uploadedImages->isEmpty()) {
            return ResponseProtocol::failed(
                null,
                'Failed to upload images',
                500
            );
        }

        return ResponseProtocol::success(
            ProductImageResource::collection($uploadedImages),
            'Images uploaded successfully',
            201
        );
    }
}
