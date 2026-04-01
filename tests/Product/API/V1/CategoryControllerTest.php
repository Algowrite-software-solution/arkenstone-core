<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_categories()
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'slug', 'parent_id', 'is_active']
                    ],
                    'meta',
                    'links'
                ]
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/v1/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_category()
    {
        $response = $this->getJson('/api/v1/categories/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_category()
    {
        $data = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic products',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/categories', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Electronics',
                    'slug' => 'electronics',
                ]
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_category()
    {
        $response = $this->postJson('/api/v1/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_unique_slug_when_creating_category()
    {
        Category::factory()->create(['slug' => 'electronics']);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_prevents_category_from_being_its_own_parent()
    {
        $category = Category::factory()->create();

        $response = $this->putJson('/api/v1/categories/' . $category->id, [
            'parent_id' => $category->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    /** @test */
    public function it_can_update_a_category()
    {
        $category = Category::factory()->create(['name' => 'Old Category']);

        $response = $this->putJson('/api/v1/categories/' . $category->id, [
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Updated Category',
                ]
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Updated Category']);
    }

    /** @test */
    public function it_can_delete_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson('/api/v1/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_can_get_category_children()
    {
        $parent = Category::factory()->create();
        $child1 = Category::factory()->create(['parent_id' => $parent->id]);
        $child2 = Category::factory()->create(['parent_id' => $parent->id]);
        Category::factory()->create(); // Unrelated category

        $response = $this->getJson('/api/v1/categories/' . $parent->id . '/children');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    /** @test */
    public function it_returns_empty_array_for_category_with_no_children()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/v1/categories/' . $category->id . '/children');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => []]);
    }

    /** @test */
    public function it_can_get_root_categories()
    {
        $root1 = Category::factory()->create(['parent_id' => null]);
        $root2 = Category::factory()->create(['parent_id' => null]);
        Category::factory()->create(['parent_id' => $root1->id]); // Child category

        $response = $this->getJson('/api/v1/categories/roots');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }
}
