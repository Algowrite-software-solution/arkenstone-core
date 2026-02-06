<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\BrandServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandService implements BrandServiceInterface
{
    public function getName(): string
    {
        return "Brand Service";
    }

    public function getAllBrands(): Collection
    {
        return Brand::orderBy('created_at', 'desc')->get();
    }

    public function getBrandById(int $id): ?Brand
    {
        return Brand::find($id);
    }

    public function createBrand(array $data): Brand
    {

        $slug = Str::slug($data['name']);
        $data['slug'] = $slug;


        $storedPath = null;

        // Handle pre-uploaded images (existing behavior)
        if (!empty($data['logo_images']) || !empty($data['logo_images'][0])) {
            Log::info("Uploaded Images", [$data['logo_images'][0]]);
            $storedPath = $this->addImage($data['logo_images'][0]);
        }

        // handle single image uplaod
        if (!empty($data['logo_image']) && !empty($data['logo_image'][0])) {
            Log::info("Uploaded Images", [$data['logo_image'][0]]);
            $storedPath = $this->addImage($data['logo_image']);
        }

        if ($storedPath) {
            $data['logo_url'] = $storedPath;
        }

        return Brand::create($data);
    }

    public function updateBrand(int $id, array $data): bool
    {
        $brand = Brand::find($id);

        // update slug if the name is provided
        if (!empty($data['name'])) {
            $slug = Str::slug($data['name']);
            $data['slug'] = $slug;
        }

        if (!$brand) {
            return false;
        }

        // delete old image
        if (!empty($brand->logo_url)) {
            $this->deleteImage($brand->logo_url);
        }

        $storedPath = null;

        // Handle pre-uploaded images (existing behavior)
        if (!empty($data['logo_images']) || !empty($data['logo_images'][0])) {
            Log::info("Uploaded Images", [$data['logo_images'][0]]);
            $storedPath = $this->addImage($data['logo_images'][0]);
        }

        // handle single image uplaod
        if (!empty($data['logo_image']) && !empty($data['logo_image'][0])) {
            Log::info("Uploaded Images", [$data['logo_image'][0]]);
            $storedPath = $this->addImage($data['logo_image']);
        }

        if ($storedPath) {
            $data['logo_url'] = $storedPath;
        }

        return $brand->update($data);
    }

    public function deleteBrand(int $id): bool
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return false;
        }

        // delete image
        if (!empty($brand->logo_url)) {
            $this->deleteImage($brand->logo_url);
        }

        return $brand->delete();
    }

    public function getActiveBrands(): Collection
    {
        return Brand::where('is_active', true)->get();
    }

    public function queryBrands(array $filters): LengthAwarePaginator
    {
        return Brand::latest()->paginate($filters['limit'] ?? 15);
    }


    public function addImage($image): ?string
    {

        $config = config('arkenstone.brand_images');
        $disk = $config['disk'] ?? 'public';
        $path = $config['path'] ?? 'brands/images';
        $useUniqueFilenames = $config['unique_filenames'] ?? true;

        if ($image instanceof UploadedFile) {
            // Store the file and get its relative path
            $storedPath = null;
            if ($useUniqueFilenames) {
                $storedPath = $image->store($path, $disk);
            } else {
                $originalName = $image->getClientOriginalName();
                $storedPath = $image->storeAs($path, $originalName, $disk);
            }

            return $storedPath;
        }

        return null;
    }

    public function deleteImage(string $path): bool
    {

        $config = config('arkenstone.brand_images');
        $disk = $config['disk'] ?? 'public';
        $path = $config['path'] ?? 'brands/images';


        // Delete the physical file from storage.
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }
}

