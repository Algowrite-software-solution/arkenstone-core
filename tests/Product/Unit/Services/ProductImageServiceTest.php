<?php

namespace Arkenstone\Core\Tests\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Arkenstone\Core\ECommerce\Product\Services\ProductImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductImageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductImageService $imageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageService = app()->make('product-image');
    }

    /** @test */
    public function it_can_get_images_by_product_id()
    {
        $product = Product::factory()->create();
        ProductImage::factory()->count(3)->create(['product_id' => $product->id]);

        $images = $this->imageService->getImagesByProductId($product->id);

        $this->assertCount(3, $images);
    }

    /** @test */
    public function it_can_get_image_by_id()
    {
        $image = ProductImage::factory()->create();

        $result = $this->imageService->getImageById($image->id);

        $this->assertNotNull($result);
        $this->assertEquals($image->id, $result->id);
    }

    /** @test */
    public function it_can_create_product_image()
    {
        $product = Product::factory()->create();

        $data = [
            'product_id' => $product->id,
            'image_url' => 'https://example.com/image.jpg',
            'alt_text' => 'Product Image',
            'is_primary' => false,
            'sort_order' => 1,
        ];

        $image = $this->imageService->createImage($data);

        $this->assertInstanceOf(ProductImage::class, $image);
        $this->assertEquals($product->id, $image->product_id);
        $this->assertDatabaseHas('product_images', ['product_id' => $product->id]);
    }

    /** @test */
    public function it_can_update_product_image()
    {
        $image = ProductImage::factory()->create(['alt_text' => 'Old Text']);

        $result = $this->imageService->updateImage($image->id, [
            'alt_text' => 'New Text',
        ]);

        $this->assertTrue($result);
        $image->refresh();
        $this->assertEquals('New Text', $image->alt_text);
    }

    /** @test */
    public function it_can_delete_product_image()
    {
        $image = ProductImage::factory()->create();

        $result = $this->imageService->deleteImage($image->id);

        $this->assertTrue($result);
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

        $result = $this->imageService->setPrimaryImage($product->id, $image2->id);

        $this->assertTrue($result);
        $image1->refresh();
        $image2->refresh();
        $this->assertFalse($image1->is_primary);
        $this->assertTrue($image2->is_primary);
    }

    /** @test */
    public function only_one_image_can_be_primary_per_product()
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

        $this->imageService->setPrimaryImage($product->id, $image2->id);

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

        $result = $this->imageService->getPrimaryImage($product->id);

        $this->assertNotNull($result);
        $this->assertEquals($primaryImage->id, $result->id);
        $this->assertTrue($result->is_primary);
    }

    /** @test */
    public function it_returns_null_when_no_primary_image_exists()
    {
        $product = Product::factory()->create();
        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
        ]);

        $result = $this->imageService->getPrimaryImage($product->id);

        $this->assertNull($result);
    }
}
