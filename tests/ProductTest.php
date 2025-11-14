<?php

namespace Arkenstone\Core\Tests;

use Arkenstone\Core\Facades\Feature1 as FacadesFeature1;

class ProductTest extends TestCase
{
    
    public function test_product_service(): void
    {
        $product = app()->make('product');
        $this->assertEquals("Product Service",$product->getName());
        $this->assertIsArray($product->getProducts([]));
        $this->assertEquals([1,2,3],$product->getAllProducts());
    }
    
}


