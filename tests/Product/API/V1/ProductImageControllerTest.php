<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductImageControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_product_images()
    {
        $product = Product::factory()->create();
        ProductImage::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->getJson('/api/v1/products/' . $product->id . '/images');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'product_id', 'image_url', 'is_primary']
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_returns_empty_array_for_product_with_no_images()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/v1/products/' . $product->id . '/images');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => []]);
    }

    /** @test */
    public function it_can_show_a_single_product_image()
    {
        $image = ProductImage::factory()->create();

        $response = $this->getJson('/api/v1/product-images/' . $image->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $image->id,
                    'image_url' => $image->image_url,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_image()
    {
        $response = $this->getJson('/api/v1/product-images/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_product_image()
    {
        $product = Product::factory()->create();

        $data = [
            'product_id' => $product->id,
            'image_url' => 'https://example.com/image.jpg',
            'alt_text' => 'Product Image',
            'is_primary' => false,
            'sort_order' => 1,
        ];

        $response = $this->postJson('/api/v1/product-images', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'product_id' => $product->id,
                    'image_url' => 'https://example.com/image.jpg',
                ]
            ]);

        $this->assertDatabaseHas('product_images', ['product_id' => $product->id]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_image()
    {
        $response = $this->postJson('/api/v1/product-images', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'image_url']);
    }

    /** @test */
    public function it_validates_product_exists_when_creating_image()
    {
        $response = $this->postJson('/api/v1/product-images', [
            'product_id' => 999,
            'image_url' => 'https://example.com/image.jpg',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function it_can_update_a_product_image()
    {
        $image = ProductImage::factory()->create(['alt_text' => 'Old Text']);

        $response = $this->putJson('/api/v1/product-images/' . $image->id, [
            'alt_text' => 'New Text',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'alt_text' => 'New Text',
                ]
            ]);

        $this->assertDatabaseHas('product_images', ['alt_text' => 'New Text']);
    }

    /** @test */
    public function it_can_delete_a_product_image()
    {
        $image = ProductImage::factory()->create();

        $response = $this->deleteJson('/api/v1/product-images/' . $image->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertSoftDeleted('product_images', ['id' => $image->id]);
    }

    /** @test */
    public function it_can_set_primary_image()
    {
        $product = Product::factory()->create();
        $image1 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
        ]);
        $image2 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
        ]);

        $response = $this->postJson('/api/v1/products/' . $product->id . '/images/' . $image2->id . '/set-primary');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $image1->refresh();
        $image2->refresh();

        $this->assertFalse($image1->is_primary);
        $this->assertTrue($image2->is_primary);
    }

    /** @test */
    public function it_ensures_only_one_image_is_primary()
    {
        $product = Product::factory()->create();
        $image1 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
        ]);
        $image2 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
        ]);

        $this->postJson('/api/v1/products/' . $product->id . '/images/' . $image2->id . '/set-primary');

        $primaryCount = ProductImage::where('product_id', $product->id)
            ->where('is_primary', true)
            ->count();

        $this->assertEquals(1, $primaryCount);
    }

    /** @test */
    public function it_can_get_primary_image()
    {
        $product = Product::factory()->create();
        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
        ]);
        $primaryImage = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
        ]);

        $response = $this->getJson('/api/v1/products/' . $product->id . '/primary-image');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $primaryImage->id,
                    'is_primary' => true,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_no_primary_image_exists()
    {
        $product = Product::factory()->create();
        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
        ]);

        $response = $this->getJson('/api/v1/products/' . $product->id . '/primary-image');

        $response->assertStatus(404);
    }
}
