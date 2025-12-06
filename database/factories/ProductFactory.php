<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Product\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $price = $this->faker->randomFloat(2, 10, 1000);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'minified_description' => $this->faker->sentence(10),
            'details' => [
                'specifications' => [
                    'weight' => $this->faker->randomFloat(2, 0.1, 10) . ' kg',
                    'dimensions' => $this->faker->randomNumber(2) . 'x' . $this->faker->randomNumber(2) . 'x' . $this->faker->randomNumber(2) . ' cm',
                    'material' => $this->faker->randomElement(['Plastic', 'Metal', 'Wood', 'Glass', 'Fabric']),
                ],
                'features' => $this->faker->sentences(3),
                'warranty' => $this->faker->randomElement(['1 year', '2 years', '3 years', 'Lifetime']),
            ],
            'sku' => strtoupper($this->faker->bothify('SKU-####-????')),
            'price' => $price,
            'discount_type' => null,
            'discount_value' => null,
            'quantity' => $this->faker->numberBetween(0, 100),
            'brand_id' => Brand::factory(),
            'is_active' => $this->faker->boolean(90),
            'is_featured' => $this->faker->boolean(20),
        ];
    }

    /**
     * Indicate that the product has a percentage discount.
     */
    public function withPercentageDiscount(float $percentage = 20): static
    {
        return $this->state(fn(array $attributes) => [
            'discount_type' => 'percentage',
            'discount_value' => $percentage,
        ]);
    }

    /**
     * Indicate that the product has a fixed amount discount.
     */
    public function withFixedDiscount(float $amount = 50): static
    {
        return $this->state(fn(array $attributes) => [
            'discount_type' => 'fixed_amount',
            'discount_value' => $amount,
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'quantity' => 0,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the product has no brand.
     */
    public function withoutBrand(): static
    {
        return $this->state(fn(array $attributes) => [
            'brand_id' => null,
        ]);
    }
}
