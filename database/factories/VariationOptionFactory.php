<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Stock\Models\VariationOption;
use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Stock\Models\VariationOption>
 */
class VariationOptionFactory extends Factory
{
    protected $model = VariationOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metaOptions = [
            ['color_code' => '#FF0000'],
            ['size_code' => 'S'],
            ['material_type' => 'Natural'],
            null,
        ];

        return [
            'variant_id' => Variant::factory(),
            'name' => $this->faker->randomElement(['Small', 'Medium', 'Large', 'Red', 'Blue', 'Green', 'Cotton', 'Polyester']),
            'meta' => $this->faker->randomElement($metaOptions),
        ];
    }
}
