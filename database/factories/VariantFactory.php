<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Stock\Models\Variant>
 */
class VariantFactory extends Factory
{
    protected $model = Variant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Size', 'Color', 'Material', 'Style', 'Capacity']),
        ];
    }
}
