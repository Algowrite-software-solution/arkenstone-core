<?php

namespace Arkenstone\Core\Tests\Stock\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Arkenstone\Core\ECommerce\Stock\Models\VariationOption;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VariationOptionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_show_a_single_variation_option()
    {
        $option = VariationOption::factory()->create();

        $response = $this->getJson('/api/v1/variation-options/' . $option->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $option->id,
                    'name' => $option->name,
                    'variant_id' => $option->variant_id,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_variation_option()
    {
        $response = $this->getJson('/api/v1/variation-options/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_variation_option()
    {
        $variant = Variant::factory()->create(['name' => 'Size']);

        $data = [
            'variant_id' => $variant->id,
            'name' => 'Large',
            'meta' => [
                'size_code' => 'L',
                'measurements' => '100x50cm',
            ],
        ];

        $response = $this->postJson('/api/v1/variation-options', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Large',
                    'variant_id' => $variant->id,
                ]
            ]);

        $this->assertDatabaseHas('variation_options', [
            'name' => 'Large',
            'variant_id' => $variant->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_variation_option()
    {
        $response = $this->postJson('/api/v1/variation-options', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['variant_id', 'name']);
    }

    /** @test */
    public function it_validates_variant_exists_when_creating_variation_option()
    {
        $response = $this->postJson('/api/v1/variation-options', [
            'variant_id' => 999,
            'name' => 'Test Option',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['variant_id']);
    }

    /** @test */
    public function it_can_update_a_variation_option()
    {
        $option = VariationOption::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson('/api/v1/variation-options/' . $option->id, [
            'name' => 'Updated Name',
            'meta' => [
                'updated' => true,
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Updated Name',
                ]
            ]);

        $this->assertDatabaseHas('variation_options', [
            'id' => $option->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_variation_option()
    {
        $response = $this->putJson('/api/v1/variation-options/999', ['name' => 'Test']);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_delete_a_variation_option()
    {
        $option = VariationOption::factory()->create();

        $response = $this->deleteJson('/api/v1/variation-options/' . $option->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('variation_options', ['id' => $option->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_variation_option()
    {
        $response = $this->deleteJson('/api/v1/variation-options/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_get_options_by_variant()
    {
        $variant = Variant::factory()->create(['name' => 'Color']);

        VariationOption::factory()->count(3)->create(['variant_id' => $variant->id]);
        VariationOption::factory()->create(); // Different variant

        $response = $this->getJson('/api/v1/variants/' . $variant->id . '/options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'variant_id']
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_returns_404_when_getting_options_for_nonexistent_variant()
    {
        $response = $this->getJson('/api/v1/variants/999/options');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_store_meta_data_as_json()
    {
        $variant = Variant::factory()->create();

        $response = $this->postJson('/api/v1/variation-options', [
            'variant_id' => $variant->id,
            'name' => 'Red',
            'meta' => [
                'hex_color' => '#FF0000',
                'rgb' => [255, 0, 0],
            ],
        ]);

        $response->assertStatus(201);

        $option = VariationOption::where('name', 'Red')->first();
        $this->assertIsArray($option->meta);
        $this->assertEquals('#FF0000', $option->meta['hex_color']);
    }
}
