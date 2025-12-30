<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxonomyControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_taxonomies()
    {
        Taxonomy::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/taxonomies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'slug', 'taxonomy_type_id']
                    ],
                    'meta',
                    'links'
                ]
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_taxonomy()
    {
        $taxonomy = Taxonomy::factory()->create();

        $response = $this->getJson('/api/v1/taxonomies/' . $taxonomy->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $taxonomy->id,
                    'name' => $taxonomy->name,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_taxonomy()
    {
        $response = $this->getJson('/api/v1/taxonomies/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_taxonomy()
    {
        $taxonomyType = TaxonomyType::factory()->create();

        $data = [
            'name' => 'Red',
            'slug' => 'red',
            'taxonomy_type_id' => $taxonomyType->id,
            'description' => 'Red color',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/taxonomies', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Red',
                    'slug' => 'red',
                ]
            ]);

        $this->assertDatabaseHas('taxonomies', ['name' => 'Red']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_taxonomy()
    {
        $response = $this->postJson('/api/v1/taxonomies', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug', 'taxonomy_type_id']);
    }

    /** @test */
    public function it_validates_taxonomy_type_exists()
    {
        $response = $this->postJson('/api/v1/taxonomies', [
            'name' => 'Red',
            'slug' => 'red',
            'taxonomy_type_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['taxonomy_type_id']);
    }

    /** @test */
    public function it_can_create_taxonomy_with_meta_data()
    {
        $taxonomyType = TaxonomyType::factory()->create();

        $response = $this->postJson('/api/v1/taxonomies', [
            'name' => 'Large',
            'slug' => 'large',
            'taxonomy_type_id' => $taxonomyType->id,
            'meta' => ['key' => 'value', 'size' => 'XL'],
        ]);

        $response->assertStatus(201);

        $taxonomy = Taxonomy::where('slug', 'large')->first();
        $this->assertEquals(['key' => 'value', 'size' => 'XL'], $taxonomy->meta);
    }

    /** @test */
    public function it_can_update_a_taxonomy()
    {
        $taxonomy = Taxonomy::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson('/api/v1/taxonomies/' . $taxonomy->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Updated Name',
                ]
            ]);

        $this->assertDatabaseHas('taxonomies', ['name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_delete_a_taxonomy()
    {
        $taxonomy = Taxonomy::factory()->create();

        $response = $this->deleteJson('/api/v1/taxonomies/' . $taxonomy->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertSoftDeleted('taxonomies', ['id' => $taxonomy->id]);
    }

    /** @test */
    public function it_can_get_taxonomies_by_type()
    {
        $colorType = TaxonomyType::factory()->create(['name' => 'Color']);
        $sizeType = TaxonomyType::factory()->create(['name' => 'Size']);

        Taxonomy::factory()->count(3)->create(['taxonomy_type_id' => $colorType->id]);
        Taxonomy::factory()->count(2)->create(['taxonomy_type_id' => $sizeType->id]);

        $response = $this->getJson('/api/v1/taxonomies/type/' . $colorType->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_returns_empty_array_for_type_with_no_taxonomies()
    {
        $taxonomyType = TaxonomyType::factory()->create();

        $response = $this->getJson('/api/v1/taxonomies/type/' . $taxonomyType->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => []]);
    }
}
