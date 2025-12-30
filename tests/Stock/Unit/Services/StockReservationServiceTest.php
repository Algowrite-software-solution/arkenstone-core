<?php

namespace Arkenstone\Core\Tests\Stock\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Arkenstone\Core\ECommerce\Stock\Models\StockReservation;
use Arkenstone\Core\ECommerce\Stock\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockReservationService $reservationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reservationService = app()->make('stock-reservation');
    }

    /** @test */
    public function it_can_reserve_stock()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
            'status' => 'active',
        ]);

        $reservation = $this->reservationService->reserve(
            $stock->id,
            10,
            ['type' => 'cart', 'id' => 1]
        );

        $this->assertInstanceOf(StockReservation::class, $reservation);
        $this->assertEquals(10, $reservation->quantity);
        $this->assertEquals('pending', $reservation->status);
        $this->assertEquals('cart', $reservation->reference_type);
        $this->assertDatabaseHas('stock_reservations', [
            'stock_id' => $stock->id,
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function it_increments_quantity_reserved_on_reservation()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
            'quantity_reserved' => 0,
            'status' => 'active',
        ]);

        $this->reservationService->reserve($stock->id, 10, ['type' => 'cart', 'id' => 1]);

        $stock->refresh();
        $this->assertEquals(10, $stock->quantity_reserved);
    }

    /** @test */
    public function it_can_release_reservation()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
            'quantity_reserved' => 10,
        ]);
        $reservation = StockReservation::factory()->create([
            'stock_id' => $stock->id,
            'quantity' => 10,
            'status' => 'pending',
        ]);

        $result = $this->reservationService->release($reservation->id);

        $this->assertTrue($result);
        $reservation->refresh();
        $this->assertEquals('cancelled', $reservation->status);

        $stock->refresh();
        $this->assertEquals(0, $stock->quantity_reserved);
    }

    /** @test */
    public function it_can_commit_reservation()
    {
        $stock = Stock::factory()->create(['quantity_on_hand' => 100]);
        $reservation = StockReservation::factory()->create([
            'stock_id' => $stock->id,
            'quantity' => 10,
            'status' => 'pending',
        ]);

        $result = $this->reservationService->commit($reservation->id);

        $this->assertTrue($result);
        $reservation->refresh();
        $this->assertEquals('committed', $reservation->status);
        $this->assertNull($reservation->expires_at);
    }

    /** @test */
    public function it_can_fulfill_reservation()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
            'quantity_reserved' => 10,
        ]);
        $reservation = StockReservation::factory()->create([
            'stock_id' => $stock->id,
            'quantity' => 10,
            'status' => 'committed',
        ]);

        $result = $this->reservationService->fulfill($reservation->id);

        $this->assertTrue($result);
        $reservation->refresh();
        $this->assertEquals('fulfilled', $reservation->status);

        $stock->refresh();
        $this->assertEquals(90, $stock->quantity_on_hand);
        $this->assertEquals(0, $stock->quantity_reserved);
    }

    /** @test */
    public function it_can_extend_reservation_expiry()
    {
        $reservation = StockReservation::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $originalExpiry = $reservation->expires_at;

        $result = $this->reservationService->extendExpiry($reservation->id, 5);

        $this->assertTrue($result);
        $reservation->refresh();
        $this->assertTrue($reservation->expires_at->greaterThan($originalExpiry));
    }

    /** @test */
    public function it_can_get_active_reservations_for_stock()
    {
        $stock = Stock::factory()->create();
        StockReservation::factory()->count(2)->create([
            'stock_id' => $stock->id,
            'status' => 'pending',
        ]);
        StockReservation::factory()->create([
            'stock_id' => $stock->id,
            'status' => 'released',
        ]);

        $active = $this->reservationService->getActiveReservations($stock->id);

        $this->assertCount(2, $active);
    }

    /** @test */
    public function it_throws_exception_when_reserving_more_than_available()
    {
        $this->expectException(\Exception::class);

        $stock = Stock::factory()->create(['quantity_on_hand' => 10]);

        $this->reservationService->reserve($stock->id, 20, ['type' => 'cart', 'id' => 1]);
    }
}
