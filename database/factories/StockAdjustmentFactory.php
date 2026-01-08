<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustment>
 */
final class StockAdjustmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(-50, 50);
        $unitCost = fake()->numberBetween(100, 10000); // in cents
        $totalCost = abs($quantity) * $unitCost;

        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'adjustment_type' => fake()->randomElement(['expired', 'damaged', 'manual', 'correction']),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'reason' => fake()->sentence(),
            'adjusted_by' => User::factory(),
            'created_at' => now(),
        ];
    }

    public function manual(): self
    {
        return $this->state(fn (array $attributes): array => [
            'adjustment_type' => 'manual',
            'quantity' => fake()->numberBetween(1, 50),
        ]);
    }

    public function damaged(): self
    {
        return $this->state(fn (array $attributes): array => [
            'adjustment_type' => 'damaged',
            'quantity' => fake()->numberBetween(-50, -1),
        ]);
    }
}
