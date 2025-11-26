<?php

namespace Arkenstone\Core\Tests\Feature\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTaxonomyControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_taxonomies_for_a_product()
    {
        $product = Product::factory()->create();
        $taxonomies = Taxonomy::factory()->count(3)->create();
        $product->taxonomies()->attach($taxonomies->pluck('id'));

        $response = $this->getJson('/api/v1/products/' . $product->id . '/taxonomies');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_returns_empty_array_for_product_with_no_taxonomies()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/v1/products/' . $product->id . '/taxonomies');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => []]);
    }

    /** @test */
    public function it_can_get_products_for_a_taxonomy()
    {
        $taxonomy = Taxonomy::factory()->create();
        $products = Product::factory()->count(3)->create();
        $taxonomy->products()->attach($products->pluck('id'));

        $response = $this->getJson('/api/v1/taxonomies/' . $taxonomy->id . '/products');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_returns_empty_array_for_taxonomy_with_no_products()
    {
        $taxonomy = Taxonomy::factory()->create();

        $response = $this->getJson('/api/v1/taxonomies/' . $taxonomy->id . '/products');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => []]);
    }

    /** @test */
    public function it_can_attach_taxonomies_to_product()
    {
        $product = Product::factory()->create();
        $taxonomies = Taxonomy::factory()->count(3)->create();

        $data = [
            'product_id' => $product->id,
            'taxonomy_ids' => $taxonomies->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/v1/products/taxonomies/attach', $data);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(3, $product->fresh()->taxonomies);
    }

    /** @test */
    public function it_validates_required_fields_when_attaching()
    {
        $response = $this->postJson('/api/v1/products/taxonomies/attach', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'taxonomy_ids']);
    }

    /** @test */
    public function it_validates_taxonomy_ids_is_array()
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/products/taxonomies/attach', [
            'product_id' => $product->id,
            'taxonomy_ids' => 'not-an-array',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['taxonomy_ids']);
    }

    /** @test */
    public function it_validates_minimum_one_taxonomy_when_attaching()
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/products/taxonomies/attach', [
            'product_id' => $product->id,
            'taxonomy_ids' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['taxonomy_ids']);
    }

    /** @test */
    public function it_reports_already_attached_taxonomies()
    {
        $product = Product::factory()->create();
        $taxonomy1 = Taxonomy::factory()->create();
        $taxonomy2 = Taxonomy::factory()->create();

        $product->taxonomies()->attach($taxonomy1->id);

        $response = $this->postJson('/api/v1/products/taxonomies/attach', [
            'product_id' => $product->id,
            'taxonomy_ids' => [$taxonomy1->id, $taxonomy2->id],
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertArrayHasKey('already_attached', $data);
        $this->assertContains($taxonomy1->id, $data['already_attached']);
    }

    /** @test */
    public function it_can_sync_taxonomies_to_product()
    {
        $product = Product::factory()->create();
        $oldTaxonomies = Taxonomy::factory()->count(2)->create();
        $product->taxonomies()->attach($oldTaxonomies->pluck('id'));

        $newTaxonomies = Taxonomy::factory()->count(3)->create();

        $response = $this->postJson('/api/v1/products/taxonomies/sync', [
            'product_id' => $product->id,
            'taxonomy_ids' => $newTaxonomies->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(3, $product->fresh()->taxonomies);
    }

    /** @test */
    public function it_can_sync_empty_array_to_detach_all()
    {
        $product = Product::factory()->create();
        $taxonomies = Taxonomy::factory()->count(3)->create();
        $product->taxonomies()->attach($taxonomies->pluck('id'));

        $response = $this->postJson('/api/v1/products/taxonomies/sync', [
            'product_id' => $product->id,
            'taxonomy_ids' => [],
        ]);

        $response->assertStatus(200);

        $this->assertCount(0, $product->fresh()->taxonomies);
    }

    /** @test */
    public function it_can_detach_taxonomies_from_product()
    {
        $product = Product::factory()->create();
        $taxonomies = Taxonomy::factory()->count(5)->create();
        $product->taxonomies()->attach($taxonomies->pluck('id'));

        $taxonomiesToDetach = $taxonomies->take(2)->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/products/taxonomies/detach', [
            'product_id' => $product->id,
            'taxonomy_ids' => $taxonomiesToDetach,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(3, $product->fresh()->taxonomies);
    }

    /** @test */
    public function it_validates_required_fields_when_detaching()
    {
        $response = $this->postJson('/api/v1/products/taxonomies/detach', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'taxonomy_ids']);
    }

    /** @test */
    public function it_reports_not_attached_taxonomies_when_detaching()
    {
        $product = Product::factory()->create();
        $attachedTaxonomy = Taxonomy::factory()->create();
        $notAttachedTaxonomy = Taxonomy::factory()->create();

        $product->taxonomies()->attach($attachedTaxonomy->id);

        $response = $this->postJson('/api/v1/products/taxonomies/detach', [
            'product_id' => $product->id,
            'taxonomy_ids' => [$attachedTaxonomy->id, $notAttachedTaxonomy->id],
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertArrayHasKey('not_found', $data);
        $this->assertContains($notAttachedTaxonomy->id, $data['not_found']);
    }
}
