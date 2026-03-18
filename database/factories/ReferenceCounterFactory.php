<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferenceCounter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferenceCounter>
 */
final class ReferenceCounterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word(),
            'last_value' => 0,
        ];
    }

    public function withKey(string $key): self
    {
        return $this->state(fn (array $attributes): array => [
            'key' => $key,
        ]);
    }

    public function startingAt(int $value): self
    {
        return $this->state(fn (array $attributes): array => [
            'last_value' => $value,
        ]);
    }
}
