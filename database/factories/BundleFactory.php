<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Illuminate\Database\Eloquent\Factories\Factory;

class BundleFactory extends Factory
{
    protected $model = Bundle::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true) . ' Bundle',
        ];
    }
}
