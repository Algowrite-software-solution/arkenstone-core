<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Stock\Enum\ReferenceType;
use Arkenstone\Core\ECommerce\Stock\Enum\StockReservationStatus;
use Arkenstone\Core\ECommerce\Stock\Models\StockReservation;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Stock\Models\StockReservation>
 */
class StockReservationFactory extends Factory
{
    protected $model = StockReservation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_id' => Stock::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(StockReservationStatus::cases())->value,
            'reference_type' => $this->faker->randomElement(ReferenceType::cases())->value,
            'reference_id' => $this->faker->numberBetween(1, 1000),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+7 days'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the reservation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StockReservationStatus::PENDING->value,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    /**
     * Indicate that the reservation is committed.
     */
    public function committed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StockReservationStatus::COMMITTED->value,
            'expires_at' => now()->addDays(3),
        ]);
    }

    /**
     * Indicate that the reservation is fulfilled.
     */
    public function fulfilled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'fulfilled',
            'expires_at' => null,
        ]);
    }

    /**
     * Indicate that the reservation is expired.
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subMinutes(5),
        ]);
    }
}
