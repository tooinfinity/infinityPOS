<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
final class InventoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'total_quantity' => fake()->numberBetween(0, 1000),
            'updated_at' => now(),
        ];
    }

    public function lowStock(): self
    {
        return $this->state(fn (array $attributes): array => [
            'total_quantity' => fake()->numberBetween(0, 10),
        ]);
    }

    public function outOfStock(): self
    {
        return $this->state(fn (array $attributes): array => [
            'total_quantity' => 0,
        ]);
    }
}
