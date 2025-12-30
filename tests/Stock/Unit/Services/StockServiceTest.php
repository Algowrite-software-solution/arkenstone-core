<?php

namespace Arkenstone\Core\Tests\Stock\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Arkenstone\Core\ECommerce\Stock\Services\StockService;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = app()->make('stock');
    }

    /** @test */
    public function it_can_create_stock()
    {
        $product = Product::factory()->create();
        $supplier = Supplier::factory()->create();

        $data = [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'sku' => 'TEST-SKU-001',
            'price' => 99.99,
            'cost' => 50.00,
            'quantity_on_hand' => 100,
            'min_stock_level' => 10,
            'status' => 'active',
        ];

        $stock = $this->stockService->createStock($data);

        $this->assertInstanceOf(Stock::class, $stock);
        $this->assertEquals('TEST-SKU-001', $stock->sku);
        $this->assertEquals(100, $stock->quantity_on_hand);
        $this->assertDatabaseHas('stocks', ['sku' => 'TEST-SKU-001']);
    }

    /** @test */
    public function it_can_get_stock_by_id()
    {
        $stock = Stock::factory()->create();

        $result = $this->stockService->getStock($stock->id);

        $this->assertNotNull($result);
        $this->assertEquals($stock->id, $result->id);
        $this->assertEquals($stock->sku, $result->sku);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_stock()
    {
        $result = $this->stockService->getStock(999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_update_stock()
    {
        $stock = Stock::factory()->create(['quantity_on_hand' => 100]);

        $updated = $this->stockService->updateStock($stock->id, [
            'quantity_on_hand' => 150,
        ]);

        $this->assertEquals(150, $updated->quantity_on_hand);
        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity_on_hand' => 150,
        ]);
    }

    /** @test */
    public function it_can_delete_stock()
    {
        $stock = Stock::factory()->create();

        $result = $this->stockService->deleteStock($stock->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('stocks', ['id' => $stock->id]);
    }

    /** @test */
    public function it_can_check_availability()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
            'status' => 'active',
        ]);

        $result = $this->stockService->checkAvailability($stock->id, 50);

        $this->assertTrue($result['available']);
        $this->assertEquals(100, $result['quantity_available']);
    }

    /** @test */
    public function it_returns_false_when_insufficient_stock()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 10,
            'status' => 'active',
        ]);

        $result = $this->stockService->checkAvailability($stock->id, 50);

        $this->assertFalse($result['available']);
        $this->assertEquals(10, $result['quantity_available']);
    }

    /** @test */
    public function it_can_get_low_stock_items()
    {
        Stock::factory()->create(['quantity_on_hand' => 5, 'min_stock_level' => 10]);
        Stock::factory()->create(['quantity_on_hand' => 100, 'min_stock_level' => 10]);

        $lowStock = $this->stockService->getLowStockItems();

        $this->assertCount(1, $lowStock);
    }

    /** @test */
    public function it_can_search_stocks_by_sku()
    {
        Stock::factory()->create(['sku' => 'LAPTOP-001']);
        Stock::factory()->create(['sku' => 'PHONE-001']);

        $results = $this->stockService->searchStocks('LAPTOP');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('LAPTOP', $results->first()->sku);
    }
}
