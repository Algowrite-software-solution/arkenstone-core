<?php

namespace Arkenstone\Core\Tests\Feature\API;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Arkenstone\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake the storage disk
        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_a_single_image_to_a_product()
    {
        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('product1.jpg', 640, 480);

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$image],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'image_url',
                        'alt_text',
                        'is_primary',
                        'sort_order',
                    ]
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Images uploaded successfully',
            ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'is_primary' => false,
        ]);

        // Verify file was stored
        $uploadedImage = ProductImage::where('product_id', $product->id)->first();
        $this->assertTrue(Storage::disk('public')->exists($uploadedImage->image_url));
    }

    /** @test */
    public function it_can_upload_multiple_images_to_a_product()
    {
        $product = Product::factory()->create();
        $images = [
            UploadedFile::fake()->image('product1.jpg', 640, 480),
            UploadedFile::fake()->image('product2.png', 800, 600),
            UploadedFile::fake()->image('product3.webp', 1024, 768),
        ];

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => $images,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('product_images', 3);

        // Verify all files were stored
        $uploadedImages = ProductImage::where('product_id', $product->id)->get();
        $this->assertCount(3, $uploadedImages);

        foreach ($uploadedImages as $uploadedImage) {
            $this->assertTrue(Storage::disk('public')->exists($uploadedImage->image_url));
        }
    }

    /** @test */
    public function it_can_upload_images_with_alt_text()
    {
        $product = Product::factory()->create();
        $images = [
            UploadedFile::fake()->image('product1.jpg'),
            UploadedFile::fake()->image('product2.jpg'),
        ];

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => $images,
            'alt_texts' => ['First product image', 'Second product image'],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'alt_text' => 'First product image',
        ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'alt_text' => 'Second product image',
        ]);
    }

    /** @test */
    public function it_can_upload_images_with_sort_order()
    {
        $product = Product::factory()->create();
        $images = [
            UploadedFile::fake()->image('product1.jpg'),
            UploadedFile::fake()->image('product2.jpg'),
        ];

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => $images,
            'sort_orders' => [10, 20],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'sort_order' => 10,
        ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'sort_order' => 20,
        ]);
    }

    /** @test */
    public function it_can_set_a_primary_image_during_upload()
    {
        $product = Product::factory()->create();
        $images = [
            UploadedFile::fake()->image('product1.jpg'),
            UploadedFile::fake()->image('product2.jpg'),
            UploadedFile::fake()->image('product3.jpg'),
        ];

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => $images,
            'primary_index' => 1, // Second image should be primary
        ]);

        $response->assertStatus(201);

        // Get all images ordered by creation
        $uploadedImages = ProductImage::where('product_id', $product->id)
            ->orderBy('id')
            ->get();

        $this->assertFalse($uploadedImages[0]->is_primary);
        $this->assertTrue($uploadedImages[1]->is_primary);
        $this->assertFalse($uploadedImages[2]->is_primary);
    }

    /** @test */
    public function it_requires_at_least_one_image()
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images']);
    }

    /** @test */
    public function it_validates_file_is_an_image()
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$file],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images.0']);
    }

    /** @test */
    public function it_validates_image_file_size()
    {
        $product = Product::factory()->create();
        // Create an image larger than the configured max size (5120 KB = 5MB)
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$largeImage],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images.0']);
    }

    /** @test */
    public function it_validates_allowed_mime_types()
    {
        $product = Product::factory()->create();
        // Create a BMP image which is not in the allowed types
        $bmpImage = UploadedFile::fake()->create('image.bmp', 100, 'image/bmp');

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$bmpImage],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images.0']);
    }

    /** @test */
    public function it_validates_alt_text_max_length()
    {
        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$image],
            'alt_texts' => [str_repeat('a', 256)], // Exceeds 255 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['alt_texts.0']);
    }

    /** @test */
    public function it_validates_sort_order_is_non_negative()
    {
        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$image],
            'sort_orders' => [-1],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_orders.0']);
    }

    /** @test */
    public function it_validates_primary_index_is_non_negative()
    {
        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$image],
            'primary_index' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['primary_index']);
    }

    /** @test */
    public function it_stores_images_in_configured_path()
    {
        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => [$image],
        ]);

        $response->assertStatus(201);

        $uploadedImage = ProductImage::where('product_id', $product->id)->first();

        // Verify the path starts with the configured path
        $configuredPath = config('arkenstone.product_images.path', 'products/images');
        $this->assertStringStartsWith($configuredPath, $uploadedImage->image_url);
    }

    /** @test */
    public function it_returns_404_for_non_existent_product()
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson("/api/v1/products/99999/images/upload", [
            'images' => [$image],
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Product not found',
            ]);

        $this->assertDatabaseCount('product_images', 0);
    }

    /** @test */
    public function it_accepts_all_allowed_image_formats()
    {
        $product = Product::factory()->create();
        $images = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.jpeg'),
            UploadedFile::fake()->image('image3.png'),
            UploadedFile::fake()->image('image4.webp'),
            UploadedFile::fake()->image('image5.gif'),
        ];

        $response = $this->postJson("/api/v1/products/{$product->id}/images/upload", [
            'images' => $images,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('product_images', 5);
    }
}
