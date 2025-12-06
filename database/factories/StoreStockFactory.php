<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreStock>
 */
final class StoreStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomNumber(2, 100),
        ];
    }

    public function withStock(float $quantity = 10): self
    {
        return $this->state(fn (): array => ['quantity' => $quantity]);
    }

    public function empty(): self
    {
        return $this->state(fn (): array => ['quantity' => 0]);
    }
}
