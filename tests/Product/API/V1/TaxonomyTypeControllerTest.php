<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxonomyTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_taxonomy_types()
    {
        TaxonomyType::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/taxonomy-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'slug']
                    ],
                    'meta',
                    'links'
                ]
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_taxonomy_type()
    {
        $taxonomyType = TaxonomyType::factory()->create();

        $response = $this->getJson('/api/v1/taxonomy-types/' . $taxonomyType->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $taxonomyType->id,
                    'name' => $taxonomyType->name,
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_taxonomy_type()
    {
        $data = [
            'name' => 'Color',
            'slug' => 'color',
            'description' => 'Product colors'
        ];

        $response = $this->postJson('/api/v1/taxonomy-types', $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Taxonomy type created successfully.'
            ]);

        $this->assertDatabaseHas('taxonomy_types', [
            'name' => 'Color',
            'slug' => 'color'
        ]);
    }

    /** @test */
    public function it_validates_name_is_required()
    {
        $data = [
            'slug' => 'color'
        ];

        $response = $this->postJson('/api/v1/taxonomy-types', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_name_is_unique()
    {
        TaxonomyType::factory()->create(['name' => 'Color']);

        $data = [
            'name' => 'Color',
            'slug' => 'color-2'
        ];

        $response = $this->postJson('/api/v1/taxonomy-types', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_update_a_taxonomy_type()
    {
        $taxonomyType = TaxonomyType::factory()->create(['name' => 'Old Name']);

        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated description'
        ];

        $response = $this->putJson('/api/v1/taxonomy-types/' . $taxonomyType->id, $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Taxonomy type updated successfully.'
            ]);

        $this->assertDatabaseHas('taxonomy_types', [
            'id' => $taxonomyType->id,
            'name' => 'Updated Name'
        ]);
    }

    /** @test */
    public function it_can_delete_a_taxonomy_type()
    {
        $taxonomyType = TaxonomyType::factory()->create();

        $response = $this->deleteJson('/api/v1/taxonomy-types/' . $taxonomyType->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Taxonomy type deleted successfully.'
            ]);

        $this->assertSoftDeleted('taxonomy_types', [
            'id' => $taxonomyType->id
        ]);
    }

    /** @test */
    public function it_returns_404_when_taxonomy_type_not_found()
    {
        $response = $this->getJson('/api/v1/taxonomy-types/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_filter_taxonomy_types_by_search()
    {
        TaxonomyType::factory()->create(['name' => 'Color']);
        TaxonomyType::factory()->create(['name' => 'Size']);
        TaxonomyType::factory()->create(['name' => 'Material']);

        $response = $this->getJson('/api/v1/taxonomy-types?search=Col');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Color', $response->json('data.data.0.name'));
    }

    /** @test */
    public function it_paginates_taxonomy_types()
    {
        TaxonomyType::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/taxonomy-types?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('data.meta.per_page', 5)
            ->assertJsonPath('data.meta.total', 20);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_validates_slug_is_unique()
    {
        TaxonomyType::factory()->create(['slug' => 'color']);

        $data = [
            'name' => 'New Color',
            'slug' => 'color'
        ];

        $response = $this->postJson('/api/v1/taxonomy-types', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_can_load_taxonomies_with_taxonomy_type()
    {
        $taxonomyType = TaxonomyType::factory()->create();
        \Arkenstone\Core\ECommerce\Product\Models\Taxonomy::factory()->count(3)->create([
            'taxonomy_type_id' => $taxonomyType->id
        ]);

        $response = $this->getJson('/api/v1/taxonomy-types/' . $taxonomyType->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'taxonomies' => [
                        '*' => ['id', 'name']
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data.taxonomies'));
    }
}