<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\ProductImageServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductImageService implements ProductImageServiceInterface
{
    public function getName(): string
    {
        return "Product Image Service";
    }

    public function getImagesByProductId(int $productId): Collection
    {
        return ProductImage::where('product_id', $productId)
            ->orderBy('sort_order')
            ->get();
    }

    public function getImageById(int $id): ?ProductImage
    {
        return ProductImage::find($id);
    }

    public function createImage(array $data): ProductImage
    {
        return ProductImage::create($data);
    }

    public function updateImage(int $id, array $data): bool
    {
        $image = ProductImage::find($id);

        if (!$image) {
            return false;
        }

        return $image->update($data);
    }

    public function deleteImage(int $id): bool
    {
        $image = ProductImage::find($id);

        if (!$image) {
            return false;
        }

        Log::info("Product Images : ", [$image]);

        // delete the image file
        if ($image->image_url) {
             try {
               Storage::disk('products')->delete($image->image_url);
               Log::info("Product Images deleted: ", [$image->image_url]);
            } catch (\Throwable $e) {
               Log::warning("Failed to delete image file: {$image->image_url}");
            }
        }

        return $image->delete();
    }

    public function setPrimaryImage(int $productId, int $imageId): bool
    {
        // First, set all images for this product to non-primary
        ProductImage::where('product_id', $productId)
            ->update(['is_primary' => false]);

        // Then set the specified image as primary
        $image = ProductImage::where('id', $imageId)
            ->where('product_id', $productId)
            ->first();

        if (!$image) {
            return false;
        }

        return $image->update(['is_primary' => true]);
    }

    public function getPrimaryImage(int $productId): ?ProductImage
    {
        return ProductImage::where('product_id', $productId)
            ->where('is_primary', true)
            ->first();
    }
}
