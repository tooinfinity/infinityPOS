<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->name());

        return [
            'name' => $name,
            'description' => $this->faker->paragraph(),
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

    public function withoutDescription(): self
    {
        return $this->state(fn (array $attributes): array => [
            'description' => null,
        ]);
    }
}
