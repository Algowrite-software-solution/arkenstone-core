<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Stock\Enum\StockStatus;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Stock\Models\Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost = $this->faker->randomFloat(2, 5, 500);
        $price = $cost * $this->faker->randomFloat(2, 1.2, 3.0);
        $quantityOnHand = $this->faker->numberBetween(0, 1000);

        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper($this->faker->bothify('STK-####-????')),
            'barcode' => $this->faker->optional()->ean13(),
            'price' => round($price, 2),
            'cost' => $cost,
            'weight' => $this->faker->randomFloat(3, 0.1, 50),
            'quantity_on_hand' => $quantityOnHand,
            'quantity_reserved' => 0,
            'min_stock_level' => $this->faker->numberBetween(5, 50),
            'supplier_id' => Supplier::factory(),
            'image_url_id' => null,
            'status' => $this->faker->randomElement(StockStatus::cases())->value,
        ];
    }

    /**
     * Indicate that the stock is available.
     */
    public function available(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StockStatus::ACTIVE->value,
            'quantity_on_hand' => $this->faker->numberBetween(10, 1000),
        ]);
    }

    /**
     * Indicate that the stock is out of stock (but still active status).
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
        ]);
    }

    /**
     * Indicate that the stock is low.
     */
    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'quantity_on_hand' => $this->faker->numberBetween(1, 10),
            'min_stock_level' => 20,
        ]);
    }

    /**
     * Set quantity reserved.
     */
    public function withReservation(int $quantity): static
    {
        return $this->state(fn(array $attributes) => [
            'quantity_reserved' => $quantity,
        ]);
    }
}
