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

        return $category->update($data);
    }

    public function deleteCategory(int $id): bool
    {
        $category = Category::find($id);

        if (!$category) {
            return false;
        }

        return $category->delete();
    }

    public function getActiveCategories(): Collection
    {
        return Category::where('is_active', true)->get();
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
}
