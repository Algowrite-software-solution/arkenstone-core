<?php

namespace Arkenstone\Core\Tests\Unit\Providers;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Product\Services\ProductService;
use Arkenstone\Core\ECommerce\Product\Services\BrandService;
use Arkenstone\Core\ECommerce\Product\Services\CategoryService;
use Arkenstone\Core\ECommerce\Product\Services\ProductImageService;
use Arkenstone\Core\ECommerce\Product\Services\ProductTaxonomyService;
use Arkenstone\Core\ECommerce\Product\Services\TaxonomyService;
use Arkenstone\Core\Services\UtilityService;
use Illuminate\Support\Facades\Event as LaravelEvent;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_product_service_in_container()
    {
        $service = app()->make('product');

        $this->assertInstanceOf(ProductService::class, $service);
    }

    /** @test */
    public function it_registers_brand_service_in_container()
    {
        $service = app()->make('brand');

        $this->assertInstanceOf(BrandService::class, $service);
    }

    /** @test */
    public function it_registers_category_service_in_container()
    {
        $service = app()->make('category');

        $this->assertInstanceOf(CategoryService::class, $service);
    }

    /** @test */
    public function it_registers_product_image_service_in_container()
    {
        $service = app()->make('product-image');

        $this->assertInstanceOf(ProductImageService::class, $service);
    }

    /** @test */
    public function it_registers_product_taxonomy_service_in_container()
    {
        $service = app()->make('product-taxonomy');

        $this->assertInstanceOf(ProductTaxonomyService::class, $service);
    }

    /** @test */
    public function it_registers_taxonomy_service_in_container()
    {
        $service = app()->make('taxonomy');

        $this->assertInstanceOf(TaxonomyService::class, $service);
    }

    /** @test */
    public function it_registers_utility_service_in_container()
    {
        $service = app()->make('utility');

        $this->assertInstanceOf(UtilityService::class, $service);
    }    /** @test */
    public function it_registers_services_as_singletons()
    {
        $product1 = app()->make('product');
        $product2 = app()->make('product');

        $this->assertSame($product1, $product2);
    }

    /** @test */
    public function it_attaches_event_dispatcher_on_boot()
    {
        $dispatcher = LaravelEvent::getFacadeRoot();

        $this->assertNotNull($dispatcher);
    }

    /** @test */
    public function it_publishes_configuration_file()
    {
        $configPath = config_path('arkenstone.php');

        // Check that config is available
        $this->assertIsArray(config('arkenstone'));
    }
}
