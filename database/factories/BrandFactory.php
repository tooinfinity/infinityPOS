<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
final class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'is_active' => $this->faker->boolean(90),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    /**
     * Mark the brand inactive.
     */
    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
