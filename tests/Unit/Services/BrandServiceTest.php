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
}
