<?php

namespace Arkenstone\Core\Tests\Unit\Services;

use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Arkenstone\Core\ECommerce\Product\Services\TaxonomyTypeService;
use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Category;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxonomyTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaxonomyTypeService $taxonomyTypeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxonomyTypeService = app()->make(TaxonomyTypeService::class);
    }

    /** @test */
    public function it_can_not_delete_taxonomy_type_if_locked()
    {
        $taxonomyType = TaxonomyType::factory()->create(['name' => 'Locked Taxonomy Type']);

        $this->assertThrows(function () use ($taxonomyType) {
            $this->taxonomyTypeService->deleteType($taxonomyType->id);
        });
    }

}
