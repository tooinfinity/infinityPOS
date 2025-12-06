<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'code' => mb_strtoupper(Str::random(6)),
            'type' => $this->faker->randomElement(['product', 'expense']),
            'is_active' => $this->faker->boolean(90),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    /**
     * Mark the category inactive.
     */
    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
