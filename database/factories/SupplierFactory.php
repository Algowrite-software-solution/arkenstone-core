<?php

namespace Arkenstone\Core\Database\Factories;

use Arkenstone\Core\ECommerce\Stock\Enum\SupplierStatus;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Arkenstone\Core\ECommerce\Stock\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postal_code' => $this->faker->postcode(),
            'supplier_code' => strtoupper($this->faker->bothify('SUP-####-??')),
            'status' => $this->faker->randomElement(SupplierStatus::cases())->value,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the supplier is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupplierStatus::ACTIVE->value,
        ]);
    }

    /**
     * Indicate that the supplier is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupplierStatus::INACTIVE->value,
        ]);
    }
}
