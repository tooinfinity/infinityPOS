<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Batch>
 */
final class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'batch_number' => mb_strtoupper(Str::random(8)),
            'cost_amount' => $this->faker->randomNumber(6),
            'quantity' => $this->faker->randomNumber(2),
            'expires_at' => now()->addMonths(12),
        ];
    }

    public function forProduct(Product $product): self
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
        ]);
    }

    public function forWarehouse(Warehouse $warehouse): self
    {
        return $this->state(fn (array $attributes): array => [
            'warehouse_id' => $warehouse->id,
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    public function expiringWithinDays(int $days): self
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->addDays($this->faker->numberBetween(1, $days)),
        ]);
    }

    public function neverExpires(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => null,
        ]);
    }

    public function empty(): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => 0,
        ]);
    }

    public function withQuantity(int $quantity): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => $quantity,
        ]);
    }
}
