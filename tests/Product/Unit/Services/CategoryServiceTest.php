<?php

namespace Arkenstone\Core\Tests\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Arkenstone\Core\ECommerce\Product\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = app()->make('category');
    }

    /** @test */
    public function it_can_get_all_categories()
    {
        Category::factory()->count(5)->create();

        $categories = $this->categoryService->getAllCategories();

        $this->assertCount(5, $categories);
    }

    /** @test */
    public function it_can_get_category_by_id()
    {
        $category = Category::factory()->create();

        $result = $this->categoryService->getCategoryById($category->id);

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result->id);
    }

    /** @test */
    public function it_can_create_category()
    {
        $data = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic products',
            'is_active' => true,
        ];

        $category = $this->categoryService->createCategory($data);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Electronics', $category->name);
        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    }

    /** @test */
    public function it_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $result = $this->categoryService->updateCategory($category->id, [
            'name' => 'New Name',
        ]);

        $this->assertTrue($result);
        $category->refresh();
        $this->assertEquals('New Name', $category->name);
    }

    /** @test */
    public function it_can_delete_category()
    {
        $category = Category::factory()->create();

        $result = $this->categoryService->deleteCategory($category->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_can_get_category_children()
    {
        $parent = Category::factory()->create();
        $child1 = Category::factory()->create(['parent_id' => $parent->id]);
        $child2 = Category::factory()->create(['parent_id' => $parent->id]);
        Category::factory()->create(); // Unrelated category

        $children = $this->categoryService->getCategoryChildren($parent->id);

        $this->assertCount(2, $children);
        $this->assertTrue($children->contains('id', $child1->id));
        $this->assertTrue($children->contains('id', $child2->id));
    }

    /** @test */
    public function it_can_get_root_categories()
    {
        $root1 = Category::factory()->create(['parent_id' => null]);
        $root2 = Category::factory()->create(['parent_id' => null]);
        Category::factory()->create(['parent_id' => $root1->id]); // Child

        $roots = $this->categoryService->getRootCategories();

        $this->assertCount(2, $roots);
        $this->assertTrue($roots->contains('id', $root1->id));
        $this->assertTrue($roots->contains('id', $root2->id));
    }

    /** @test */
    public function root_categories_have_null_parent_id()
    {
        Category::factory()->count(3)->create(['parent_id' => null]);

        $roots = $this->categoryService->getRootCategories();

        $roots->each(function ($category) {
            $this->assertNull($category->parent_id);
        });
    }
}
