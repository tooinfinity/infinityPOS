<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Unit;
use App\Models\User;
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
            default => mb_strtolower(mb_substr((string) $name, 0, 3)),
        };

        return [
            'name' => $name,
            'short_name' => $this->faker->optional()->randomElement([$short, mb_strtoupper($short)]),
            'is_active' => $this->faker->boolean(95),
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
