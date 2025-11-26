<?php

namespace Arkenstone\Core\Tests;

use Arkenstone\Core\ECommerce\Product\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_service(): void
    {
        $product = app()->make('product');
        $this->assertInstanceOf(ProductService::class, $product);
    }
}


