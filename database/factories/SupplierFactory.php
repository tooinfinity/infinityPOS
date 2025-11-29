<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
final class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->companyEmail(),
            'address' => $this->faker->optional()->address(),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'is_active' => $this->faker->boolean(95),
            'business_identifier_id' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'is_active' => true]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'is_active' => false]);
    }
}
