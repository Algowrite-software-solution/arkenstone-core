<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\Product\ProductContract;
use Arkenstone\Core\ECommerce\Contracts\ProductServiceInterface;
use Arkenstone\Core\ECommerce\Product\Events\ProductCreated;
use Arkenstone\Core\ECommerce\Product\Events\ProductDeleted;
use Arkenstone\Core\ECommerce\Product\Events\ProductImageDeleted;
use Arkenstone\Core\ECommerce\Product\Events\ProductUpdated;
use Arkenstone\Core\ECommerce\Product\Events\ProductViewed;
use Arkenstone\Core\ECommerce\Product\Helper\ProductFilter;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Arkenstone\Core\ECommerce\Product\Events\ProductImagesUploaded;
use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Exception;

class ProductService implements ProductServiceInterface
{
   /**
    * A whitelist of relations that are safe to be eager-loaded.
    * @var array
    */
   protected array $allowedRelations = ['categories', 'brand', 'images', 'taxonomies', 'taxonomies.type', 'stocks.variationOptions.variant', 'bundle', 'bundle.items.product'];
   protected int $PER_PAGE;
   protected string $ORDER;

   protected BundleService $bundleService;

   public function __construct(BundleService $bundleService)
   {
      $this->bundleService = $bundleService;
      $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
      $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
   }

   public function find(int $id, array $with = []): ?ProductContract
   {

      $relationsToLoad = empty($with) ? $this->allowedRelations : $with;
      $product = Product::with($relationsToLoad)->find($id);

      // trigger Event
      if ($product) {
         ProductViewed::dispatch($product); // viewd a single product
      }

      return $product;
   }

   public function search(array $filters): LengthAwarePaginator
   {
      $relationsToLoad = isset($filters['with']) & !empty($filters['with']) ? $filters['with'] : $this->allowedRelations;

      Log::info("Relations to Load 123");

      $query = Product::query()->with($relationsToLoad);

      $filter = new ProductFilter($query, $filters); // filter the query based on the provided filters
      $filteredQuery = $filter->apply();

      return $filteredQuery->orderBy($filters['order_by'] ?? 'created_at', $filters['order'] ?? $this->ORDER)->paginate($filters['per_page'] ?? $this->PER_PAGE);
   }

   public function create(array $data): ProductContract
   {
      return DB::transaction(function () use ($data) {
         $data["slug"] = Str::slug($data["name"]); // create a slug

         // Extract image-related fields before creating product
         $uploadedImages = $data['images'] ?? [];
         $imageMetadata = [
            'alt_texts' => $data['image_alt_texts'] ?? [],
            'sort_orders' => $data['image_sort_orders'] ?? [],
            'primary_index' => $data['primary_image_index'] ?? null,
         ];

         // Remove image fields from product data
         unset($data['images'], $data['image_alt_texts'], $data['image_sort_orders'], $data['primary_image_index']);

         $product = Product::create($data);

         if (!empty($data['category_ids'])) {
            $product->categories()->attach($data['category_ids']);
         }

         if (!empty($data['taxonomy_ids'])) {
            $product->taxonomies()->attach($data['taxonomy_ids']);
         }

         // Handle pre-uploaded images (existing behavior)
         if (!empty($data['images'])) {
            Log::info("Uploaded Images", [$data['images']]);

            foreach ($data['images'] as $image) {
               $productImage = ProductImage::find($image["id"] ?? null);
               if ($productImage) {
                  $productImage->update([
                     'product_id' => $product->id,
                  ]);
               }
            }
         }

         // Handle file uploads (new behavior)
         if (!empty($uploadedImages)) {
            $this->addImages($uploadedImages, $product->id, $imageMetadata);
         }

         // Handle bundle assignment validation
         if (!empty($data['bundle_id'])) {
            $bundle = Bundle::find($data['bundle_id']);
            if ($bundle && $this->bundleService->bundleContainsProduct($bundle, $product->id)) {
               throw new Exception("Recursion detected: Bundle '{$bundle->name}' contains this product, so it cannot be assigned to it.");
            }
         }

         ProductCreated::dispatch($product);

         return $product->fresh(['images', 'primaryImage', 'categories', 'taxonomies', 'taxonomies.type', 'bundle.items.product']);
      });
   }

   public function update(ProductContract|Product $product, array $data): ProductContract
   {
      return DB::transaction(function () use ($product, $data) {
         // Extract image-related fields
         $uploadedImages = $data['images'] ?? [];
         $deleteImageIds = $data['delete_image_ids'] ?? [];
         $deleteImageUrls = $data['delete_image_urls'] ?? [];
         $imageMetadata = [
            'alt_texts' => $data['image_alt_texts'] ?? [],
            'sort_orders' => $data['image_sort_orders'] ?? [],
            'primary_index' => $data['primary_image_index'] ?? null,
         ];

         // Remove image fields from product data
         unset($data['images'], $data['image_alt_texts'], $data['image_sort_orders'], $data['primary_image_index'], $data['delete_image_ids'], $data['delete_image_urls']);

         $product->update($data);

         if (isset($data['category_ids'])) {
            $product->categories()->sync($data['category_ids']);
         }

         if (isset($data['taxonomy_ids'])) {
            $product->taxonomies()->sync($data['taxonomy_ids']);
         }

         // Delete specified images
         if (!empty($deleteImageIds)) {
            foreach ($deleteImageIds as $imageId) {
               $image = ProductImage::where('id', $imageId)
                  ->where('product_id', $product->id)
                  ->first();
               if ($image) {
                  $this->deleteImage($imageId);
               }
            }
         }

         // Delete Specified images by URL
         if (!empty($deleteImageUrls)) {
            foreach ($deleteImageUrls as $imageUrl) {
               // #TODO : check the actual file locatoin in the local drive and if it was under product directory, delete it
            }
         }

         // delete miages by image id
         Log::info("Info updated 1");
         if (!empty($deleteImageIds)) {
            Log::info("Info updated 2");
            ProductImage::whereIn('id', $deleteImageIds)->delete();
         }

         // Handle new file uploads
         if (!empty($uploadedImages)) {
            $this->addImages($uploadedImages, $product->id, $imageMetadata);
         }

         if (isset($data['variants'])) {
            $existingVariantIds = collect($data['variants'])->pluck('id')->filter()->toArray();
            $product->variants()->whereNotIn('id', $existingVariantIds)->delete();
            foreach ($data['variants'] as $variantData) {
               $variant = $product->variants()->updateOrCreate(['id' => $variantData['id'] ?? null], $variantData);
               if (isset($variantData['attribute_value_ids'])) {
                  $variant->attributeValues()->sync($variantData['attribute_value_ids']);
               }
            }
         }

         // Handle bundle assignment validation
         if (isset($data['bundle_id'])) {
            // If bundle_id is being cleared (null), no validation needed.
            // If it's being set/changed:
            if ($data['bundle_id'] !== $product->bundle_id && !is_null($data['bundle_id'])) {
               $bundle = Bundle::find($data['bundle_id']);
               if ($bundle && $this->bundleService->bundleContainsProduct($bundle, $product->id)) {
                  throw new Exception("Recursion detected: Bundle '{$bundle->name}' contains this product, so it cannot be assigned to it.");
               }
            }
         }

         ProductUpdated::dispatch($product->fresh());
         return $product->fresh(['images', 'primaryImage', 'categories', 'taxonomies', 'taxonomies.type', 'bundle.items.product']);
      });
   }


   public function delete(ProductContract|Product $product): bool
   {
      DB::beginTransaction();

      try {
         // Delete all product images (DB + disk)
         foreach ($product->images as $image) {
            try {
               Storage::disk('products')->delete($image->url);
            } catch (\Throwable $e) {
               Log::warning("Failed to delete image file: {$image->url}");
            }
         }
         $product->images()->delete();

         // Detach pivot table relations (not delete related models!)
         $product->categories()->detach();
         $product->taxonomies()->detach();

         // Finally delete product itself
         $result = $product->delete();

         DB::commit();

         if ($result) {
            ProductDeleted::dispatch($product);
         }

         return $result;
      } catch (\Throwable $th) {
         DB::rollBack();
         Log::error("Product deletion failed: " . $th->getMessage());
         return false;
      }
   }

   /**
    * Add multiple images to a product.
    *
    * @param array $images Array of UploadedFile instances
    * @param int|null $productId Product ID to associate images with
    * @param array $metadata Optional metadata (alt_texts, sort_orders, primary_index)
    * @return Collection Collection of created ProductImage models
    */
   public function addImages(array $images, ?int $productId = null, array $metadata = []): Collection
   {
      $createdImages = new Collection();
      $config = config('arkenstone.product_images');
      $disk = $config['disk'] ?? 'public';
      $path = $config['path'] ?? 'products/images';
      $useUniqueFilenames = $config['unique_filenames'] ?? true;

      DB::transaction(function () use ($images, $productId, $metadata, $disk, $path, $useUniqueFilenames, &$createdImages) {
         foreach ($images as $index => $imageFile) {
            if ($imageFile instanceof UploadedFile) {
               // Store the file and get its relative path
               if ($useUniqueFilenames) {
                  $storedPath = $imageFile->store($path, $disk);
               } else {
                  $originalName = $imageFile->getClientOriginalName();
                  $storedPath = $imageFile->storeAs($path, $originalName, $disk);
               }



               // Prepare image data
               $imageData = [
                  'image_url' => $storedPath,
                  'is_primary' => false,
               ];

               // Add product_id if provided
               if ($productId !== null) {
                  $imageData['product_id'] = $productId;
               }

               // Add alt_text if provided
               if (isset($metadata['alt_texts'][$index])) {
                  $imageData['alt_text'] = $metadata['alt_texts'][$index];
               }

               // Add sort_order if provided
               if (isset($metadata['sort_orders'][$index])) {
                  $imageData['sort_order'] = $metadata['sort_orders'][$index];
               }

               // Create the database record for the image
               $newImage = ProductImage::create($imageData);

               $createdImages->push($newImage);
            }
         }

         // Set primary image if specified
         if (isset($metadata['primary_index']) && $createdImages->isNotEmpty()) {
            $primaryIndex = $metadata['primary_index'];
            if (isset($createdImages[$primaryIndex])) {
               $createdImages[$primaryIndex]->update(['is_primary' => true]);
            }
         }
      });

      if ($createdImages->isNotEmpty()) {
         ProductImagesUploaded::dispatch($createdImages);
      }

      return $createdImages;
   }

   public function deleteImage($image_id): bool
   {

      $image = ProductImage::find($image_id);

      if (!$image) {
         return false;
      }

      // The relative path is stored in the 'image_url' property
      $path = $image->image_url;

      // Delete the physical file from storage.
      if (Storage::disk('public')->exists($path)) {
         Storage::disk('public')->delete($path);
      }

      // Then delete the database record.
      $result = $image->delete();

      // If the deletion was successful, dispatch the event.
      if ($result) {
         ProductImageDeleted::dispatch($image);
      }

      return $result;
   }


   public function deleteImageByUrl(string $image_url): bool
   {
      return true;
   }
}
