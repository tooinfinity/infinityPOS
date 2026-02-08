<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
final class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'code' => $this->faker->unique()->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function withName(string $name): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $name,
        ]);
    }

    public function withCode(string $code): self
    {
        return $this->state(fn (array $attributes): array => [
            'code' => $code,
        ]);
    }

    public function withoutEmail(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email' => null,
        ]);
    }

    public function withoutPhone(): self
    {
        return $this->state(fn (array $attributes): array => [
            'phone' => null,
        ]);
    }

    public function inCity(string $city): self
    {
        return $this->state(fn (array $attributes): array => [
            'city' => $city,
        ]);
    }

    public function inCountry(string $country): self
    {
        return $this->state(fn (array $attributes): array => [
            'country' => $country,
        ]);
    }
}
