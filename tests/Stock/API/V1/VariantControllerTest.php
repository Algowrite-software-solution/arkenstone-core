<?php

namespace Arkenstone\Core\Tests\Stock\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VariantControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_variants()
    {
        Variant::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/variants');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name']
                    ],
                    'meta',
                    'links'
                ]
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_variant()
    {
        $variant = Variant::factory()->create(['name' => 'Size']);

        $response = $this->getJson('/api/v1/variants/' . $variant->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $variant->id,
                    'name' => 'Size',
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_variant()
    {
        $response = $this->getJson('/api/v1/variants/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_variant()
    {
        $data = [
            'name' => 'Color',
        ];

        $response = $this->postJson('/api/v1/variants', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Color',
                ]
            ]);

        $this->assertDatabaseHas('variants', ['name' => 'Color']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_variant()
    {
        $response = $this->postJson('/api/v1/variants', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_update_a_variant()
    {
        $variant = Variant::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson('/api/v1/variants/' . $variant->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Updated Name',
                ]
            ]);

        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_variant()
    {
        $response = $this->putJson('/api/v1/variants/999', ['name' => 'Test']);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_delete_a_variant()
    {
        $variant = Variant::factory()->create();

        $response = $this->deleteJson('/api/v1/variants/' . $variant->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('variants', ['id' => $variant->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_variant()
    {
        $response = $this->deleteJson('/api/v1/variants/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_search_variants()
    {
        Variant::factory()->create(['name' => 'Size']);
        Variant::factory()->create(['name' => 'Color']);
        Variant::factory()->create(['name' => 'Material']);

        $response = $this->getJson('/api/v1/variants?search=Size');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Size', $data[0]['name']);
    }
}
