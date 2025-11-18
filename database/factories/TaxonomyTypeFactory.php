<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Product\Models\TaxonomyType>
 */
class TaxonomyTypeFactory extends Factory
{
    protected $model = TaxonomyType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Color', 'Size', 'Material', 'Style', 'Feature', 'Weight', 'Dimension', 'Pattern', 'Finish', 'Grade']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
