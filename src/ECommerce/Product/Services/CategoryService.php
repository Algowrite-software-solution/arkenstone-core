<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\CategoryServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService implements CategoryServiceInterface
{

    public int $PER_PAGE;
    public string $ORDER;

    public function __construct()
    {
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }

    public function getName(): string
    {
        return "Category Service";
    }

    public function getAllCategories(array $filters = []): Collection
    {
        return Category::orderBy('created_at', $this->ORDER)->when(
            !($filters['with_inactive'] ?? false),
        fn($q) => $q->where('is_active', true)
        )->get();
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function createCategory(array $data): Category
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $storedPath = null;
        if (!empty($data['image'])) {
            $storedPath = $this->addImage($data['image']);
        }

        if ($storedPath) {
            $data['image_url'] = $storedPath;
        }

        return Category::create($data);
    }

    public function updateCategory(int $id, array $data): bool
    {
        $data['slug'] ??= Str::slug($data['name']); // #TODO - temp fix
        $data['is_active'] ??= true; // #TODO - temp fix
        $category = Category::find($id);

        if (!$category) {
            return false;
        }

        $storedPath = null;
        if (!empty($data['image'])) {
            $storedPath = $this->addImage($data['image']);
        }

        if ($storedPath) {
            // Delete old image if it exists
            if (!empty($category->image_url)) {
                $this->deleteImage($category->image_url);
            }
            $data['image_url'] = $storedPath;
        }

        return $category->update($data);
    }

    public function deleteCategory(int $id): bool
    {
        $category = Category::find($id);

        if (!$category) {
            return false;
        }

        if (!empty($category->image_url)) {
            $this->deleteImage($category->image_url);
        }

        return $category->delete();
    }

    public function getActiveCategories(): Collection
    {
        return Category::orderBy('created_at', $this->ORDER)->where('is_active', true)->get();
    }

    public function getCategoryChildren(int $id): Collection
    {
        $category = Category::find($id);

        if (!$category) {
            return new Collection();
        }

        return $category->children;
    }

    public function getRootCategories(): Collection
    {
        return Category::whereNull('parent_id')->get();
    }

    public function addImage($image): ?string
    {
        $config = config('arkenstone.category_images');
        $disk = $config['disk'] ?? 'categories';
        $path = $config['path'] ?? 'categories/images';
        $useUniqueFilenames = $config['unique_filenames'] ?? true;


        // Applied for both single image uplaods and arrya of images. for array of images the first element item will be selected
        if ($image instanceof \Illuminate\Http\UploadedFile || (is_array($image) && isset($image[0]) && $image[0] instanceof \Illuminate\Http\UploadedFile)) {
            $file = is_array($image) ? $image[0] : $image;
            $storedPath = null;
            if ($useUniqueFilenames) {
                $storedPath = $file->store($path, $disk);
            }
            else {
                $originalName = $file->getClientOriginalName();
                $storedPath = $file->storeAs($path, $originalName, $disk);
            }

            return $storedPath;
        }

        return null;
    }

    public function deleteImage(string $path): bool
    {
        $config = config('arkenstone.category_images');
        $disk = $config['disk'] ?? 'categories';

        // Delete the physical file from storage.
        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);
        }

        return false;
    }
}