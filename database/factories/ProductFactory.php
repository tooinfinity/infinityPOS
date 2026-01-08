<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
final class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'barcode' => fake()->optional()->ean13(),
            'description' => fake()->optional()->sentence(),
            'unit' => fake()->randomElement(['piece', 'gram', 'milliliter']),
            'selling_price' => fake()->numberBetween(100, 100000), // in cents
            'alert_quantity' => fake()->numberBetween(5, 50),
            'image' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
