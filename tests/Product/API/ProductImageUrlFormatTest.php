<?php

namespace Arkenstone\Core\Tests\Feature\API;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Arkenstone\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductImageUrlFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_returns_relative_image_urls_with_storage_prefix()
    {
        $product = Product::factory()->create();
        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_url' => 'products/images/test-image.jpg',
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);

        $responseData = $response->json('data');

        // Check that images are included
        $this->assertArrayHasKey('images', $responseData);
        $this->assertCount(1, $responseData['images']);

        // Verify the image URL format
        $imageData = $responseData['images'][0];
        $this->assertArrayHasKey('image_url', $imageData);

        // Should start with 'storage/' and NOT contain 'http://' or 'https://'
        $imageUrl = $imageData['image_url'];
        $this->assertStringStartsWith('storage/', $imageUrl);
        $this->assertStringNotContainsString('http://', $imageUrl);
        $this->assertStringNotContainsString('https://', $imageUrl);

        // Should be: storage/products/images/test-image.jpg
        $this->assertEquals('storage/products/images/test-image.jpg', $imageUrl);
    }

    /** @test */
    public function it_preserves_absolute_urls_for_external_images()
    {
        $product = Product::factory()->create();
        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_url' => 'https://cdn.example.com/images/external.jpg',
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);

        $responseData = $response->json('data');
        $imageData = $responseData['images'][0];

        // External URLs should remain unchanged
        $this->assertEquals('https://cdn.example.com/images/external.jpg', $imageData['image_url']);
    }

    /** @test */
    public function it_uses_relative_url_helper_method()
    {
        $image = ProductImage::factory()->make([
            'image_url' => 'products/images/my-image.png',
        ]);

        $relativeUrl = $image->getRelativeUrl();

        $this->assertEquals('storage/products/images/my-image.png', $relativeUrl);
        $this->assertStringStartsWith('storage/', $relativeUrl);
    }

    /** @test */
    public function it_keeps_public_url_helper_for_backward_compatibility()
    {
        $image = ProductImage::factory()->make([
            'image_url' => 'products/images/my-image.png',
        ]);

        $publicUrl = $image->getPublicUrl();

        // getPublicUrl() should still return absolute URL with asset() helper
        $this->assertStringContainsString('storage/products/images/my-image.png', $publicUrl);
    }
}
