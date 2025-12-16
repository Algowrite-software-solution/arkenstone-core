<?php

namespace Arkenstone\Core\Tests\Feature\API;

use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductNewFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_product_with_minified_description()
    {
        $brand = Brand::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'price' => 99.99,
            'description' => 'This is a long description with many details about the product.',
            'minified_description' => 'Short summary of the product.',
            'brand_id' => $brand->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'minified_description' => 'Short summary of the product.',
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'minified_description' => 'Short summary of the product.',
        ]);
    }

    /** @test */
    public function it_can_create_product_with_details_json()
    {
        $brand = Brand::factory()->create();

        $detailsData = [
            'specifications' => [
                'weight' => '2.5 kg',
                'dimensions' => '30x20x10 cm',
                'material' => 'Plastic',
            ],
            'features' => ['Waterproof', 'Durable', 'Lightweight'],
            'warranty' => '2 years',
        ];

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-002',
            'price' => 149.99,
            'details' => $detailsData,
            'brand_id' => $brand->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'details' => $detailsData,
            ]);

        $product = Product::where('sku', 'TEST-SKU-002')->first();
        $this->assertNotNull($product);
        $this->assertEquals($detailsData, $product->details);
        $this->assertIsArray($product->details);
    }

    /** @test */
    public function it_can_create_product_with_both_new_fields()
    {
        $brand = Brand::factory()->create();

        $detailsData = [
            'specifications' => ['color' => 'Blue'],
            'features' => ['Feature 1', 'Feature 2'],
        ];

        $productData = [
            'name' => 'Complete Product',
            'sku' => 'TEST-SKU-003',
            'price' => 199.99,
            'description' => 'Full product description goes here.',
            'minified_description' => 'Brief summary.',
            'details' => $detailsData,
            'brand_id' => $brand->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'minified_description' => 'Brief summary.',
                'details' => $detailsData,
            ]);
    }

    /** @test */
    public function it_can_update_product_minified_description()
    {
        $product = Product::factory()->create([
            'minified_description' => 'Old summary',
        ]);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => $product->name,
            'minified_description' => 'Updated summary',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'minified_description' => 'Updated summary',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'minified_description' => 'Updated summary',
        ]);
    }

    /** @test */
    public function it_can_update_product_details()
    {
        $oldDetails = ['old' => 'data'];
        $product = Product::factory()->create([
            'details' => $oldDetails,
        ]);

        $newDetails = [
            'specifications' => ['updated' => 'spec'],
            'features' => ['New feature'],
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => $product->name,
            'details' => $newDetails,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'details' => $newDetails,
            ]);

        $product->refresh();
        $this->assertEquals($newDetails, $product->details);
    }

    /** @test */
    public function it_validates_minified_description_max_length()
    {
        $brand = Brand::factory()->create();

        $longDescription = str_repeat('a', 501); // Exceeds 500 char limit

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-004',
            'price' => 99.99,
            'minified_description' => $longDescription,
            'brand_id' => $brand->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['minified_description']);
    }

    /** @test */
    public function it_validates_details_must_be_array()
    {
        $brand = Brand::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-005',
            'price' => 99.99,
            'details' => 'not an array', // Invalid type
            'brand_id' => $brand->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['details']);
    }

    /** @test */
    public function it_allows_null_minified_description()
    {
        $brand = Brand::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-006',
            'price' => 99.99,
            'minified_description' => null,
            'brand_id' => $brand->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);

        $product = Product::where('sku', 'TEST-SKU-006')->first();
        $this->assertNull($product->minified_description);
    }

    /** @test */
    public function it_allows_null_details()
    {
        $brand = Brand::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-007',
            'price' => 99.99,
            'details' => null,
            'brand_id' => $brand->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);

        $product = Product::where('sku', 'TEST-SKU-007')->first();
        $this->assertNull($product->details);
    }

    /** @test */
    public function it_casts_details_to_array_automatically()
    {
        $detailsData = [
            'color' => 'Red',
            'size' => 'Large',
        ];

        $product = Product::factory()->create([
            'details' => $detailsData,
        ]);

        // Refresh from database to test casting
        $product->refresh();

        $this->assertIsArray($product->details);
        $this->assertEquals($detailsData, $product->details);
        $this->assertEquals('Red', $product->details['color']);
        $this->assertEquals('Large', $product->details['size']);
    }

    /** @test */
    public function it_includes_new_fields_in_api_response()
    {
        $detailsData = [
            'specifications' => ['weight' => '1kg'],
            'features' => ['Compact'],
        ];

        $product = Product::factory()->create([
            'minified_description' => 'API test summary',
            'details' => $detailsData,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'minified_description',
                    'details',
                ]
            ])
            ->assertJsonFragment([
                'minified_description' => 'API test summary',
                'details' => $detailsData,
            ]);
    }

    /** @test */
    public function it_can_list_products_with_new_fields()
    {
        $product = Product::factory()->create([
            'name' => 'Product With New Fields',
            'minified_description' => 'Summary for listing',
            'details' => ['category' => 'Electronics'],
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);

        // Just verify we can fetch products successfully
        $this->assertTrue(count($response->json('data')) > 0);
    }

    /** @test */
    public function factory_generates_valid_new_fields()
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->minified_description);
        $this->assertIsString($product->minified_description);
        $this->assertLessThanOrEqual(500, strlen($product->minified_description));

        $this->assertNotNull($product->details);
        $this->assertIsArray($product->details);
        $this->assertArrayHasKey('specifications', $product->details);
        $this->assertArrayHasKey('features', $product->details);
        $this->assertArrayHasKey('warranty', $product->details);
    }
}
