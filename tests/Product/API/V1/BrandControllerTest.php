<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_brands()
    {
        Brand::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/brands');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'slug', 'is_active']
                    ],
                    'meta',
                    'links'
                ]
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_brand()
    {
        $brand = Brand::factory()->create();

        $response = $this->getJson('/api/v1/brands/' . $brand->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_brand()
    {
        $response = $this->getJson('/api/v1/brands/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_brand()
    {
        $data = [
            'name' => 'Nike',
            'slug' => 'nike',
            'description' => 'Just Do It',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/brands', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Nike',
                    'slug' => 'nike',
                ]
            ]);

        $this->assertDatabaseHas('brands', ['name' => 'Nike']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_brand()
    {
        $response = $this->postJson('/api/v1/brands', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    /** @test */
    public function it_validates_unique_slug_when_creating_brand()
    {
        Brand::factory()->create(['slug' => 'nike']);

        $response = $this->postJson('/api/v1/brands', [
            'name' => 'Nike',
            'slug' => 'nike',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_can_update_a_brand()
    {
        $brand = Brand::factory()->create(['name' => 'Old Brand']);

        $response = $this->putJson('/api/v1/brands/' . $brand->id, [
            'name' => 'Updated Brand',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Updated Brand',
                ]
            ]);

        $this->assertDatabaseHas('brands', ['name' => 'Updated Brand']);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_brand()
    {
        $response = $this->putJson('/api/v1/brands/999', ['name' => 'Test']);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_delete_a_brand()
    {
        $brand = Brand::factory()->create();

        $response = $this->deleteJson('/api/v1/brands/' . $brand->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_brand()
    {
        $response = $this->deleteJson('/api/v1/brands/999');

        $response->assertStatus(404);
    }
}
