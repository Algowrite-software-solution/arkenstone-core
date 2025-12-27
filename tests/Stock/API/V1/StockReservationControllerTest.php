<?php

namespace Arkenstone\Core\Tests\Stock\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Arkenstone\Core\ECommerce\Stock\Models\StockReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_show_a_reservation()
    {
        $reservation = StockReservation::factory()->create();

        $response = $this->getJson('/api/v1/stock-reservations/' . $reservation->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $reservation->id,
                    'quantity' => $reservation->quantity,
                    'status' => $reservation->status,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_reservation()
    {
        $response = $this->getJson('/api/v1/stock-reservations/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_reserve_stock()
    {
        $stock = Stock::factory()->available()->create([
            'quantity_on_hand' => 100,
            'quantity_reserved' => 0,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/stock-reservations/reserve', [
            'stock_id' => $stock->id,
            'quantity' => 10,
            'reference_type' => 'cart',
            'reference_id' => 123,
            'minutes' => 15,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'quantity' => 10,
                    'status' => 'pending',
                    'reference_type' => 'cart',
                ]
            ]);

        $this->assertDatabaseHas('stock_reservations', [
            'stock_id' => $stock->id,
            'quantity' => 10,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_reserving_stock()
    {
        $response = $this->postJson('/api/v1/stock-reservations/reserve', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_id', 'quantity']);
    }

    /** @test */
    public function it_cannot_reserve_more_than_available_quantity()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 10,
            'quantity_reserved' => 0,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/stock-reservations/reserve', [
            'stock_id' => $stock->id,
            'quantity' => 20,
            'reference_type' => 'cart',
            'reference_id' => 123,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_extend_reservation_expiry()
    {
        $reservation = StockReservation::factory()->pending()->create([
            'expires_at' => now()->addMinutes(10),
        ]);

        $originalExpiry = $reservation->expires_at;

        $response = $this->postJson('/api/v1/stock-reservations/' . $reservation->id . '/extend', [
            'minutes' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $reservation->refresh();
        $this->assertTrue($reservation->expires_at->greaterThan($originalExpiry));
    }

    /** @test */
    public function it_validates_minutes_when_extending_reservation()
    {
        $reservation = StockReservation::factory()->create();

        $response = $this->postJson('/api/v1/stock-reservations/' . $reservation->id . '/extend', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['minutes']);
    }

    /** @test */
    public function it_can_release_reservation()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
            'quantity_reserved' => 10,
            'status' => 'active',
        ]);

        $reservation = StockReservation::factory()->pending()->create([
            'stock_id' => $stock->id,
            'quantity' => 10,
        ]);

        $response = $this->postJson('/api/v1/stock-reservations/' . $reservation->id . '/release');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $reservation->refresh();
        $this->assertEquals('cancelled', $reservation->status);

        $stock->refresh();
        $this->assertEquals(0, $stock->quantity_reserved);
    }

    /** @test */
    public function it_can_commit_reservation()
    {
        $stock = Stock::factory()->create([
            'quantity_on_hand' => 100,
            'quantity_reserved' => 10,
            'status' => 'active',
        ]);

        $reservation = StockReservation::factory()->pending()->create([
            'stock_id' => $stock->id,
            'quantity' => 10,
        ]);

        $response = $this->postJson('/api/v1/stock-reservations/' . $reservation->id . '/commit');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

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
            'status' => 'active',
        ]);

        $reservation = StockReservation::factory()->committed()->create([
            'stock_id' => $stock->id,
            'quantity' => 10,
        ]);

        $response = $this->postJson('/api/v1/stock-reservations/' . $reservation->id . '/fulfill');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $reservation->refresh();
        $this->assertEquals('fulfilled', $reservation->status);

        $stock->refresh();
        $this->assertEquals(90, $stock->quantity_on_hand);
        $this->assertEquals(0, $stock->quantity_reserved);
    }

    /** @test */
    public function it_can_update_reservation_status()
    {
        $reservation = StockReservation::factory()->pending()->create();

        $response = $this->postJson('/api/v1/stock-reservations/' . $reservation->id . '/update-status', [
            'status' => 'expired',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $reservation->refresh();
        $this->assertEquals('expired', $reservation->status);
    }

    /** @test */
    public function it_validates_status_when_updating_reservation()
    {
        $reservation = StockReservation::factory()->create();

        $response = $this->postJson('/api/v1/stock-reservations/' . $reservation->id . '/update-status', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_get_active_reservations_for_stock()
    {
        $stock = Stock::factory()->create(['status' => 'active']);

        StockReservation::factory()->pending()->count(2)->create([
            'stock_id' => $stock->id,
        ]);

        StockReservation::factory()->fulfilled()->create([
            'stock_id' => $stock->id,
        ]);

        $response = $this->getJson('/api/v1/stock-reservations/stock/' . $stock->id . '/active');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_can_get_reservations_by_reference()
    {
        StockReservation::factory()->count(2)->create([
            'reference_type' => 'order',
            'reference_id' => 123,
        ]);

        StockReservation::factory()->create([
            'reference_type' => 'cart',
            'reference_id' => 456,
        ]);

        $response = $this->getJson('/api/v1/stock-reservations/by-reference?reference_type=order&reference_id=123');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_validates_reference_parameters_when_getting_by_reference()
    {
        $response = $this->getJson('/api/v1/stock-reservations/by-reference');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_type', 'reference_id']);
    }
}
