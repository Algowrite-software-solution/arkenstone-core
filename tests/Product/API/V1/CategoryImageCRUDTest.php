<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryImageCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Fake the disk configured in arkenstone.php
        Storage::fake('categories');
    }

    /** @test */
    public function it_can_create_a_category_with_an_image()
    {
        $image = UploadedFile::fake()->image('category.jpg');

        $data = [
            'name' => 'Furniture',
            'slug' => 'furniture',
            'image' => $image,
            'alt_text' => 'Beautiful furniture',
        ];

        $response = $this->postJson('/api/v1/categories', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Furniture',
                    'alt_text' => 'Beautiful furniture',
                ]
            ]);

        $category = Category::first();
        $this->assertNotNull($category->image_url);
        $this->assertEquals('Beautiful furniture', $category->alt_text);

        // Verify file exists on disk
        Storage::disk('categories')->assertExists($category->image_url);
    }

    /** @test */
    public function it_can_update_a_category_image()
    {
        $category = Category::factory()->create([
            'image_url' => 'categories/images/old.jpg',
            'alt_text' => 'Old Alt'
        ]);
        
        // Ensure old file "exists" in the fake disk
        Storage::disk('categories')->put('categories/images/old.jpg', 'content');

        $newImage = UploadedFile::fake()->image('new_category.png');

        $data = [
            'name' => 'Office Furniture',
            'image' => $newImage,
            'alt_text' => 'Modern office chair',
        ];

        $response = $this->putJson('/api/v1/categories/' . $category->id, $data);

        $response->assertStatus(200);

        $category->refresh();
        $this->assertNotEquals('categories/images/old.jpg', $category->image_url);
        $this->assertEquals('Modern office chair', $category->alt_text);

        // Verify new file exists and old file is deleted
        Storage::disk('categories')->assertExists($category->image_url);
        Storage::disk('categories')->assertMissing('categories/images/old.jpg');
    }

    /** @test */
    public function it_deletes_image_file_when_category_is_deleted()
    {
        $category = Category::factory()->create([
            'image_url' => 'categories/images/to_be_deleted.jpg'
        ]);
        Storage::disk('categories')->put('categories/images/to_be_deleted.jpg', 'content');

        $response = $this->deleteJson('/api/v1/categories/' . $category->id);

        $response->assertStatus(200);
        
        // Verify disk is empty or at least the specific file is gone
        Storage::disk('categories')->assertMissing('categories/images/to_be_deleted.jpg');
    }

    /** @test */
    public function it_validates_image_file_type()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $data = [
            'name' => 'Invalid Category',
            'image' => $invalidFile,
        ];

        $response = $this->postJson('/api/v1/categories', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function it_validates_image_max_size()
    {
        // Max size in config is 5120 KB (5MB)
        $largeFile = UploadedFile::fake()->image('huge.jpg')->size(6000);

        $data = [
            'name' => 'Too Large Category',
            'image' => $largeFile,
        ];

        $response = $this->postJson('/api/v1/categories', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function it_can_handle_image_upload_as_array_item()
    {
        // Frontend sometimes sends image as an array item (e.g. from a file manager)
        $image = UploadedFile::fake()->image('item.jpg');

        $data = [
            'name' => 'Array Image Category',
            'image' => [$image],
        ];

        $response = $this->postJson('/api/v1/categories', $data);

        $response->assertStatus(201);
        
        $category = Category::where('name', 'Array Image Category')->first();
        $this->assertNotNull($category->image_url);
        Storage::disk('categories')->assertExists($category->image_url);
    }
}
