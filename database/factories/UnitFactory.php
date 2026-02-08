<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
final class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var string $name */
        $name = $this->faker->unique()->randomElement([
            'Piece', 'Kilogram', 'Gram', 'Liter', 'Milliliter', 'Meter', 'Centimeter', 'Box', 'Pack', 'Dozen',
        ]);

        $short = match ($name) {
            'Piece' => 'pc',
            'Kilogram' => 'kg',
            'Gram' => 'g',
            'Liter' => 'l',
            'Milliliter' => 'ml',
            'Meter' => 'm',
            'Centimeter' => 'cm',
            'Box' => 'box',
            'Pack' => 'pack',
            'Dozen' => 'dz',
            default => mb_strtolower(mb_substr($name, 0, 3)),
        };

        return [
            'name' => $name,
            'short_name' => $this->faker->randomElement([$short, mb_strtoupper($short)]),
            'is_active' => $this->faker->boolean(95),
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

    public function piece(): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Piece',
            'short_name' => 'pc',
        ]);
    }

    public function kilogram(): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Kilogram',
            'short_name' => 'kg',
        ]);
    }

    public function liter(): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Liter',
            'short_name' => 'l',
        ]);
    }

    public function box(): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Box',
            'short_name' => 'box',
        ]);
    }

    public function withName(string $name, string $shortName): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $name,
            'short_name' => $shortName,
        ]);
    }
}
