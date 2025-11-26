<?php

namespace Arkenstone\Core\Tests\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Arkenstone\Core\ECommerce\Product\Services\BrandService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrandServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BrandService $brandService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->brandService = app()->make('brand');
    }

    /** @test */
    public function it_can_get_all_brands()
    {
        Brand::factory()->count(3)->create();

        $brands = $this->brandService->getAllBrands();

        $this->assertCount(3, $brands);
    }

    /** @test */
    public function it_can_get_brand_by_id()
    {
        $brand = Brand::factory()->create();

        $result = $this->brandService->getBrandById($brand->id);

        $this->assertNotNull($result);
        $this->assertEquals($brand->id, $result->id);
        $this->assertEquals($brand->name, $result->name);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_brand()
    {
        $result = $this->brandService->getBrandById(999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_create_brand()
    {
        $data = [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test Description',
            'is_active' => true,
        ];

        $brand = $this->brandService->createBrand($data);

        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertEquals('Test Brand', $brand->name);
        $this->assertEquals('test-brand', $brand->slug);
        $this->assertTrue($brand->is_active);
        $this->assertDatabaseHas('brands', ['name' => 'Test Brand']);
    }

    /** @test */
    public function it_can_update_brand()
    {
        $brand = Brand::factory()->create(['name' => 'Original Name']);

        $result = $this->brandService->updateBrand($brand->id, [
            'name' => 'Updated Name',
        ]);

        $this->assertTrue($result);
        $brand->refresh();
        $this->assertEquals('Updated Name', $brand->name);
    }

    /** @test */
    public function it_returns_false_when_updating_nonexistent_brand()
    {
        $result = $this->brandService->updateBrand(999, ['name' => 'Test']);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_delete_brand()
    {
        $brand = Brand::factory()->create();

        $result = $this->brandService->deleteBrand($brand->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    /** @test */
    public function it_returns_false_when_deleting_nonexistent_brand()
    {
        $result = $this->brandService->deleteBrand(999);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_query_brands_with_pagination()
    {
        Brand::factory()->count(20)->create();

        $result = $this->brandService->queryBrands(['limit' => 10]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    /** @test */
    public function it_uses_default_limit_when_not_provided()
    {
        Brand::factory()->count(20)->create();

        $result = $this->brandService->queryBrands([]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(15, $result->items());
        $this->assertEquals(15, $result->perPage());
    }

    /** @test */
    public function it_returns_brands_ordered_by_latest()
    {
        $oldBrand = Brand::factory()->create(['name' => 'Old Brand']);
        sleep(1);
        $newBrand = Brand::factory()->create(['name' => 'New Brand']);

        $result = $this->brandService->queryBrands(['limit' => 10]);

        $this->assertEquals($newBrand->id, $result->items()[0]->id);
        $this->assertEquals($oldBrand->id, $result->items()[1]->id);
    }
}
