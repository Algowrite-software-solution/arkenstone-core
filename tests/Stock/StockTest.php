<?php

namespace Arkenstone\Core\Tests\Stock;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_service(): void
    {
        $stock = app()->make('stock');
        $this->assertInstanceOf(StockService::class, $stock);
    }
}
