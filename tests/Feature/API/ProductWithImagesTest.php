<?php

namespace Arkenstone\Core\Tests\Feature\API;

use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Arkenstone\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductWithImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_create_product_without_images()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'sku' => 'TEST-SKU-001',
            'brand_id' => $brand->id,
            'category_ids' => [$category->id],
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'price',
                    'sku',
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
        ]);
    }

    /** @test */
    public function it_can_create_product_with_images()
    {
        $brand = Brand::factory()->create();
        $image1 = UploadedFile::fake()->image('product1.jpg', 640, 480);
        $image2 = UploadedFile::fake()->image('product2.png', 800, 600);

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Product with Images',
            'price' => 149.99,
            'sku' => 'TEST-SKU-002',
            'brand_id' => $brand->id,
            'uploaded_images' => [$image1, $image2],
        ]);

        $response->assertStatus(201);

        $product = Product::where('sku', 'TEST-SKU-002')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);

        // Verify files were uploaded
        foreach ($product->images as $image) {
            $this->assertTrue(Storage::disk('public')->exists($image->image_url));
        }
    }

    /** @test */
    public function it_can_create_product_with_images_and_metadata()
    {
        $brand = Brand::factory()->create();
        $image1 = UploadedFile::fake()->image('front.jpg');
        $image2 = UploadedFile::fake()->image('back.jpg');

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Product with Metadata',
            'price' => 199.99,
            'sku' => 'TEST-SKU-003',
            'brand_id' => $brand->id,
            'uploaded_images' => [$image1, $image2],
            'image_alt_texts' => ['Front view', 'Back view'],
            'image_sort_orders' => [1, 2],
            'primary_image_index' => 0,
        ]);

        $response->assertStatus(201);

        $product = Product::where('sku', 'TEST-SKU-003')->first();
        $images = $product->images()->orderBy('id')->get();

        $this->assertEquals('Front view', $images[0]->alt_text);
        $this->assertEquals('Back view', $images[1]->alt_text);
        $this->assertEquals(1, $images[0]->sort_order);
        $this->assertEquals(2, $images[1]->sort_order);
        $this->assertTrue($images[0]->is_primary);
        $this->assertFalse($images[1]->is_primary);
    }

    /** @test */
    public function it_validates_uploaded_images_during_product_creation()
    {
        $brand = Brand::factory()->create();
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'sku' => 'TEST-SKU-004',
            'brand_id' => $brand->id,
            'uploaded_images' => [$invalidFile],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['uploaded_images.0']);
    }

    /** @test */
    public function it_validates_image_file_size_during_product_creation()
    {
        $brand = Brand::factory()->create();
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'sku' => 'TEST-SKU-005',
            'brand_id' => $brand->id,
            'uploaded_images' => [$largeImage],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['uploaded_images.0']);
    }

    /** @test */
    public function it_can_update_product_without_images()
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'price' => 129.99,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 129.99,
        ]);
    }

    /** @test */
    public function it_can_update_product_and_add_new_images()
    {
        $product = Product::factory()->create();
        $image1 = UploadedFile::fake()->image('new1.jpg');
        $image2 = UploadedFile::fake()->image('new2.jpg');

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated with Images',
            'uploaded_images' => [$image1, $image2],
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertCount(2, $product->images);

        foreach ($product->images as $image) {
            $this->assertTrue(Storage::disk('public')->exists($image->image_url));
        }
    }

    /** @test */
    public function it_can_update_product_and_delete_images()
    {
        $product = Product::factory()->create();
        $image1 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_url' => 'products/images/test1.jpg',
        ]);
        $image2 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_url' => 'products/images/test2.jpg',
        ]);

        // Store fake files
        Storage::disk('public')->put('products/images/test1.jpg', 'fake content');
        Storage::disk('public')->put('products/images/test2.jpg', 'fake content');

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product',
            'delete_image_ids' => [$image1->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('product_images', ['id' => $image1->id]);
        $this->assertDatabaseHas('product_images', ['id' => $image2->id]);

        // Verify file was deleted
        $this->assertFalse(Storage::disk('public')->exists('products/images/test1.jpg'));
    }

    /** @test */
    public function it_can_update_product_add_and_delete_images_simultaneously()
    {
        $product = Product::factory()->create();
        $existingImage = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image_url' => 'products/images/existing.jpg',
        ]);
        Storage::disk('public')->put('products/images/existing.jpg', 'fake content');

        $newImage = UploadedFile::fake()->image('new.jpg');

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product',
            'delete_image_ids' => [$existingImage->id],
            'uploaded_images' => [$newImage],
            'image_alt_texts' => ['New image alt text'],
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertCount(1, $product->images);
        $newUploadedImage = $product->images->first();
        $this->assertEquals('New image alt text', $newUploadedImage->alt_text);
        $this->assertTrue(Storage::disk('public')->exists($newUploadedImage->image_url));
        $this->assertFalse(Storage::disk('public')->exists('products/images/existing.jpg'));
    }

    /** @test */
    public function it_validates_delete_image_ids_belong_to_product()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $imageOfProduct2 = ProductImage::factory()->create(['product_id' => $product2->id]);

        $response = $this->putJson("/api/v1/products/{$product1->id}", [
            'name' => 'Updated Product',
            'delete_image_ids' => [$imageOfProduct2->id],
        ]);

        // Image should not be deleted because it belongs to a different product
        $response->assertStatus(200);
        $this->assertDatabaseHas('product_images', ['id' => $imageOfProduct2->id]);
    }

    /** @test */
    public function it_can_set_primary_image_during_product_update()
    {
        $product = Product::factory()->create();
        $existingImage = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
        ]);

        $newImage1 = UploadedFile::fake()->image('new1.jpg');
        $newImage2 = UploadedFile::fake()->image('new2.jpg');

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'uploaded_images' => [$newImage1, $newImage2],
            'primary_image_index' => 1, // Second new image should be primary
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $newImages = $product->images()->where('id', '!=', $existingImage->id)->orderBy('id')->get();

        $this->assertFalse($newImages[0]->is_primary);
        $this->assertTrue($newImages[1]->is_primary);
    }

    /** @test */
    public function it_can_create_product_with_mixed_operations()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Complete Product',
            'price' => 299.99,
            'sku' => 'COMPLETE-001',
            'brand_id' => $brand->id,
            'category_ids' => [$category->id],
            'uploaded_images' => [$image],
            'image_alt_texts' => ['Main product image'],
            'is_active' => true,
        ]);

        $response->assertStatus(201);

        $product = Product::where('sku', 'COMPLETE-001')->first();
        $this->assertNotNull($product);
        $this->assertCount(1, $product->categories);
        $this->assertCount(1, $product->images);
        $this->assertEquals('Main product image', $product->images->first()->alt_text);
    }    /** @test */
    public function it_stores_images_in_configured_path_during_product_creation()
    {
        $brand = Brand::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'sku' => 'TEST-SKU-PATH',
            'brand_id' => $brand->id,
            'uploaded_images' => [$image],
        ]);

        $response->assertStatus(201);

        $product = Product::where('sku', 'TEST-SKU-PATH')->first();
        $uploadedImage = $product->images->first();

        $this->assertTrue(Storage::disk('public')->exists($uploadedImage->image_url));
        $configuredPath = config('arkenstone.product_images.path', 'products/images');
        $this->assertStringStartsWith($configuredPath, $uploadedImage->image_url);
    }

    /** @test */
    public function it_can_create_product_with_multiple_categories_and_images()
    {
        $brand = Brand::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Multi Category Product',
            'price' => 299.99,
            'sku' => 'MULTI-CAT-001',
            'brand_id' => $brand->id,
            'category_ids' => [$category1->id, $category2->id],
            'uploaded_images' => [$image],
        ]);

        $response->assertStatus(201);

        $product = Product::where('sku', 'MULTI-CAT-001')->first();
        $this->assertCount(2, $product->categories);
        $this->assertCount(1, $product->images);
    }
}
