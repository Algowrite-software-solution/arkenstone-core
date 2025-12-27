<?php

namespace Arkenstone\Core\Tests\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Arkenstone\Core\ECommerce\Product\Services\TaxonomyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxonomyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaxonomyService $taxonomyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxonomyService = app()->make('taxonomy');
    }

    /** @test */
    public function it_can_list_taxonomies_with_pagination()
    {
        Taxonomy::factory()->count(20)->create();

        $result = $this->taxonomyService->listTaxonomies(['per_page' => 10]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    /** @test */
    public function it_uses_default_pagination_when_not_provided()
    {
        Taxonomy::factory()->count(20)->create();

        $result = $this->taxonomyService->listTaxonomies();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(15, $result->perPage());
    }

    /** @test */
    public function it_can_filter_taxonomies_by_type_id()
    {
        $type1 = TaxonomyType::factory()->create();
        $type2 = TaxonomyType::factory()->create();

        Taxonomy::factory()->count(3)->create(['taxonomy_type_id' => $type1->id]);
        Taxonomy::factory()->count(2)->create(['taxonomy_type_id' => $type2->id]);

        $result = $this->taxonomyService->listTaxonomies(['taxonomy_type_id' => $type1->id]);

        $this->assertCount(3, $result->items());
        $result->each(function ($taxonomy) use ($type1) {
            $this->assertEquals($type1->id, $taxonomy->taxonomy_type_id);
        });
    }

    /** @test */
    public function it_can_filter_taxonomies_by_type_slug()
    {
        $colorType = TaxonomyType::factory()->create(['slug' => 'color']);
        $sizeType = TaxonomyType::factory()->create(['slug' => 'size']);

        Taxonomy::factory()->count(3)->create(['taxonomy_type_id' => $colorType->id]);
        Taxonomy::factory()->count(2)->create(['taxonomy_type_id' => $sizeType->id]);

        $result = $this->taxonomyService->listTaxonomies(['type_slug' => 'color']);

        $this->assertCount(3, $result->items());
        $result->each(function ($taxonomy) use ($colorType) {
            $this->assertEquals($colorType->id, $taxonomy->taxonomy_type_id);
        });
    }

    /** @test */
    public function it_can_filter_taxonomies_by_parent_id()
    {
        $parent = Taxonomy::factory()->create();
        $otherParent = Taxonomy::factory()->create();

        Taxonomy::factory()->count(3)->create(['parent_id' => $parent->id]);
        Taxonomy::factory()->count(2)->create(['parent_id' => $otherParent->id]);

        $result = $this->taxonomyService->listTaxonomies(['parent_id' => $parent->id]);

        $this->assertCount(3, $result->items());
        $result->each(function ($taxonomy) use ($parent) {
            $this->assertEquals($parent->id, $taxonomy->parent_id);
        });
    }

    /** @test */
    public function it_can_filter_root_taxonomies_only()
    {
        $parent = Taxonomy::factory()->create(['parent_id' => null]);
        Taxonomy::factory()->count(2)->create(['parent_id' => null]);
        Taxonomy::factory()->count(3)->create(['parent_id' => $parent->id]);

        $result = $this->taxonomyService->listTaxonomies(['root_only' => true]);

        $this->assertCount(3, $result->items());
        $result->each(function ($taxonomy) {
            $this->assertNull($taxonomy->parent_id);
        });
    }

    /** @test */
    public function it_can_search_taxonomies_by_name()
    {
        Taxonomy::factory()->create(['name' => 'Red Color']);
        Taxonomy::factory()->create(['name' => 'Blue Color']);
        Taxonomy::factory()->create(['name' => 'Large Size']);

        $result = $this->taxonomyService->listTaxonomies(['search' => 'Color']);

        $this->assertCount(2, $result->items());
        $result->each(function ($taxonomy) {
            $this->assertStringContainsString('Color', $taxonomy->name);
        });
    }

    /** @test */
    public function it_loads_relationships_when_listing_taxonomies()
    {
        $type = TaxonomyType::factory()->create();
        $parent = Taxonomy::factory()->create(['taxonomy_type_id' => $type->id]);
        $child = Taxonomy::factory()->create([
            'taxonomy_type_id' => $type->id,
            'parent_id' => $parent->id
        ]);

        $result = $this->taxonomyService->listTaxonomies();

        $this->assertTrue($result->items()[0]->relationLoaded('type'));
        $this->assertTrue($result->items()[0]->relationLoaded('parent'));
        $this->assertTrue($result->items()[0]->relationLoaded('children'));
    }

    /** @test */
    public function it_can_combine_multiple_filters()
    {
        $colorType = TaxonomyType::factory()->create(['slug' => 'color']);
        $parent = Taxonomy::factory()->create(['taxonomy_type_id' => $colorType->id]);

        Taxonomy::factory()->create([
            'taxonomy_type_id' => $colorType->id,
            'parent_id' => $parent->id,
            'name' => 'Red'
        ]);
        Taxonomy::factory()->create([
            'taxonomy_type_id' => $colorType->id,
            'parent_id' => $parent->id,
            'name' => 'Blue'
        ]);
        Taxonomy::factory()->create([
            'taxonomy_type_id' => $colorType->id,
            'parent_id' => $parent->id,
            'name' => 'Large'
        ]);

        $result = $this->taxonomyService->listTaxonomies([
            'type_slug' => 'color',
            'parent_id' => $parent->id,
            'search' => 'Red'
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Red', $result->items()[0]->name);
    }

    /** @test */
    public function it_can_create_taxonomy()
    {
        $type = TaxonomyType::factory()->create();

        $data = [
            'taxonomy_type_id' => $type->id,
            'name' => 'Test Taxonomy',
            'slug' => 'test-taxonomy',
            'is_active' => true,
        ];

        $taxonomy = $this->taxonomyService->createTaxonomy($data);

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertEquals('Test Taxonomy', $taxonomy->name);
        $this->assertEquals('test-taxonomy', $taxonomy->slug);
        $this->assertDatabaseHas('taxonomies', ['name' => 'Test Taxonomy']);
    }

    /** @test */
    public function it_can_update_taxonomy()
    {
        $taxonomy = Taxonomy::factory()->create(['name' => 'Old Name']);

        $updated = $this->taxonomyService->updateTaxonomy($taxonomy, [
            'name' => 'New Name',
        ]);

        $this->assertInstanceOf(Taxonomy::class, $updated);
        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('taxonomies', ['name' => 'New Name']);
    }

    /** @test */
    public function it_can_delete_taxonomy()
    {
        $taxonomy = Taxonomy::factory()->create();

        $result = $this->taxonomyService->deleteTaxonomy($taxonomy);

        $this->assertTrue($result);
        $this->assertSoftDeleted('taxonomies', ['id' => $taxonomy->id]);
    }

    /** @test */
    public function it_can_get_active_taxonomies()
    {
        Taxonomy::factory()->count(3)->create(['is_active' => true]);
        Taxonomy::factory()->count(2)->create(['is_active' => false]);

        $active = $this->taxonomyService->getActiveTaxonomies();

        $this->assertCount(3, $active);
        $active->each(function ($taxonomy) {
            $this->assertTrue($taxonomy->is_active);
        });
    }
}
