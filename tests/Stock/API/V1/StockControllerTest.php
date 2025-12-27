<?php

namespace Arkenstone\Core\Tests\Stock\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_stocks()
    {
        Stock::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/stocks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'sku', 'price', 'quantity_on_hand', 'status']
                    ],
                    'meta',
                    'links'
                ]
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_stock()
    {
        $stock = Stock::factory()->available()->create();

        $response = $this->getJson('/api/v1/stocks/' . $stock->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $stock->id,
                    'sku' => $stock->sku,
                    'status' => $stock->status,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_stock()
    {
        $response = $this->getJson('/api/v1/stocks/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_stock()
    {
        $product = Product::factory()->create();
        $supplier = Supplier::factory()->create();

        $data = [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'sku' => 'STOCK-12345',
            'barcode' => '1234567890123',
            'price' => 199.99,
            'cost' => 150.00,
            'weight' => 2.5,
            'quantity_on_hand' => 100,
            'min_stock_level' => 10,
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/stocks', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'sku' => 'STOCK-12345',
                    'price' => '199.99',
                ]
            ]);

        $this->assertDatabaseHas('stocks', ['sku' => 'STOCK-12345']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_stock()
    {
        $response = $this->postJson('/api/v1/stocks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'sku', 'price']);
    }

    /** @test */
    public function it_validates_unique_sku_when_creating_stock()
    {
        Stock::factory()->create(['sku' => 'UNIQUE-SKU-123']);

        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/stocks', [
            'product_id' => $product->id,
            'sku' => 'UNIQUE-SKU-123',
            'price' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /** @test */
    public function it_can_update_stock()
    {
        $stock = Stock::factory()->create([
            'price' => 100,
            'quantity_on_hand' => 50,
        ]);

        $response = $this->putJson('/api/v1/stocks/' . $stock->id, [
            'price' => 150,
            'quantity_on_hand' => 75,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'price' => '150.00',
                    'quantity_on_hand' => 75,
                ]
            ]);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'price' => 150,
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_stock()
    {
        $response = $this->putJson('/api/v1/stocks/999', ['price' => 100]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_delete_stock()
    {
        $stock = Stock::factory()->create();

        $response = $this->deleteJson('/api/v1/stocks/' . $stock->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertSoftDeleted('stocks', ['id' => $stock->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_stock()
    {
        $response = $this->deleteJson('/api/v1/stocks/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_search_stocks_by_sku()
    {
        Stock::factory()->create(['sku' => 'LAPTOP-001']);
        Stock::factory()->create(['sku' => 'DESKTOP-001']);
        Stock::factory()->create(['sku' => 'PHONE-001']);

        $response = $this->getJson('/api/v1/stocks/search?search=LAPTOP');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('LAPTOP', $data[0]['sku']);
    }

    /** @test */
    public function it_can_get_low_stock_items()
    {
        Stock::factory()->lowStock()->count(3)->create();
        Stock::factory()->available()->count(2)->create([
            'quantity_on_hand' => 1000,
            'min_stock_level' => 10,
        ]);

        $response = $this->getJson('/api/v1/stocks/low-stock');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    /** @test */
    public function it_can_get_out_of_stock_items()
    {
        Stock::factory()->outOfStock()->count(2)->create();
        Stock::factory()->available()->count(3)->create();

        $response = $this->getJson('/api/v1/stocks/out-of-stock');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    /** @test */
    public function it_can_check_stock_availability()
    {
        $stock = Stock::factory()->available()->create([
            'quantity_on_hand' => 100,
            'quantity_reserved' => 0,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/stocks/check-availability', [
            'stock_id' => $stock->id,
            'quantity' => 50,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'available' => true,
                    'quantity_available' => 100,
                ]
            ]);
    }

    /** @test */
    public function it_returns_false_when_checking_insufficient_stock()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 10,
            'quantity_reserved' => 0,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/stocks/check-availability', [
            'stock_id' => $stock->id,
            'quantity' => 50,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'available' => false,
                    'quantity_available' => 10,
                ]
            ]);
    }

    /** @test */
    public function it_can_adjust_stock_quantity()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
        ]);

        $response = $this->postJson('/api/v1/stocks/' . $stock->id . '/adjust-quantity', [
            'quantity' => 20,
            'reason' => 'Inventory adjustment',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $stock->refresh();
        $this->assertEquals(120, $stock->quantity_on_hand);
    }

    /** @test */
    public function it_can_decrease_stock_quantity_with_negative_value()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
        ]);

        $response = $this->postJson('/api/v1/stocks/' . $stock->id . '/adjust-quantity', [
            'quantity' => -30,
            'reason' => 'Damaged items removed',
        ]);

        $response->assertStatus(200);

        $stock->refresh();
        $this->assertEquals(70, $stock->quantity_on_hand);
    }

    /** @test */
    public function it_validates_quantity_when_adjusting_stock()
    {
        $stock = Stock::factory()->create();

        $response = $this->postJson('/api/v1/stocks/' . $stock->id . '/adjust-quantity', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }
}
