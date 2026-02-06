<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
final class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'received_quantity' => $this->faker->numberBetween(1, 100),
            'unit_cost' => $this->faker->numberBetween(1, 1000),
            'subtotal' => $this->faker->numberBetween(1, 10000),
            'expires_at' => now()->addMonths(12),
        ];
    }
}
