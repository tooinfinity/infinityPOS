<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BusinessIdentifier;
use App\Models\Supplier;
use App\Models\User;
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
            'is_active' => $this->faker->boolean(95),
            'business_identifier_id' => BusinessIdentifier::factory(),
            'created_by' => User::factory(),
            'updated_by' => null,
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
