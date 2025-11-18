<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Product\Models\Taxonomy>
 */
class TaxonomyFactory extends Factory
{
    protected $model = Taxonomy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 10000),
            'description' => $this->faker->sentence(),
            'taxonomy_type_id' => TaxonomyType::factory(),
            'parent_id' => null,
            'meta' => null,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the taxonomy has a parent.
     */
    public function withParent(?int $parentId = null): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => $parentId ?? Taxonomy::factory(),
        ]);
    }

    /**
     * Indicate that the taxonomy has metadata.
     */
    public function withMeta(array $meta): static
    {
        return $this->state(fn(array $attributes) => [
            'meta' => $meta,
        ]);
    }

    /**
     * Indicate that the taxonomy is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the taxonomy is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }
}
