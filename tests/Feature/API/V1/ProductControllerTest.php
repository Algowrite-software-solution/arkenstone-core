<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_products()
    {
        Product::factory()->active()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'slug', 'price']
                    ],
                    'meta',
                    'links'
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_products_by_name()
    {
        Product::factory()->active()->create(['name' => 'Laptop Computer']);
        Product::factory()->active()->create(['name' => 'Desktop Computer']);
        Product::factory()->active()->create(['name' => 'Smartphone']);

        $response = $this->getJson('/api/v1/products?name=Laptop');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Laptop', $data[0]['name']);
    }

    /** @test */
    public function it_can_filter_products_by_brand()
    {
        $brand = Brand::factory()->create();
        Product::factory()->active()->count(2)->create(['brand_id' => $brand->id]);
        Product::factory()->active()->create(); // Different brand

        $response = $this->getJson('/api/v1/products?brand_id=' . $brand->id);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    /** @test */
    public function it_can_filter_products_by_min_price()
    {
        Product::factory()->active()->create(['price' => 100]);
        Product::factory()->active()->create(['price' => 200]);
        Product::factory()->active()->create(['price' => 300]);

        $response = $this->getJson('/api/v1/products?min_price=150');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    /** @test */
    public function it_can_filter_products_by_max_price()
    {
        Product::factory()->active()->create(['price' => 100]);
        Product::factory()->active()->create(['price' => 200]);
        Product::factory()->active()->create(['price' => 300]);

        $response = $this->getJson('/api/v1/products?max_price=250');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    /** @test */
    public function it_can_filter_products_by_categories()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $product1 = Product::factory()->active()->create();
        $product1->categories()->attach($category1->id);

        $product2 = Product::factory()->active()->create();
        $product2->categories()->attach($category2->id);

        $response = $this->getJson('/api/v1/products?category_ids[]=' . $category1->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    /** @test */
    public function it_can_filter_active_products_only()
    {
        Product::factory()->count(2)->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/products?is_active=1');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_product()
    {
        $product = Product::factory()->active()->create();

        $response = $this->getJson('/api/v1/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_product()
    {
        $response = $this->getJson('/api/v1/products/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $brand = Brand::factory()->create();

        $data = [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test Description',
            'sku' => 'SKU-12345',
            'price' => 99.99,
            'stock_quantity' => 10,
            'brand_id' => $brand->id,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/products', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Test Product',
                    'sku' => 'SKU-12345',
                ]
            ]);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_product()
    {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_unique_sku_when_creating_product()
    {
        $existingProduct = Product::factory()->active()->create(['sku' => 'UNIQUE-SKU']);

        $response = $this->postJson('/api/v1/products', [
            'name' => 'New Product',
            'sku' => 'UNIQUE-SKU',
            'price' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $product = Product::factory()->active()->create(['name' => 'Old Name']);

        $response = $this->putJson('/api/v1/products/' . $product->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Updated Name',
                ]
            ]);

        $this->assertDatabaseHas('products', ['name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_attach_categories_when_creating_product()
    {
        $brand = Brand::factory()->create();
        $categories = Category::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'sku' => 'SKU-123',
            'price' => 100,
            'brand_id' => $brand->id,
            'category_ids' => $categories->pluck('id')->toArray(),
        ]);

        $response->assertStatus(201);

        $product = Product::where('sku', 'SKU-123')->first();
        $this->assertCount(2, $product->categories);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $product = Product::factory()->active()->create();

        $response = $this->deleteJson('/api/v1/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_product()
    {
        $response = $this->deleteJson('/api/v1/products/999');

        $response->assertStatus(404);
    }
}
