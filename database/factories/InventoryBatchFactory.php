<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryBatch>
 */
final class InventoryBatchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantityReceived = fake()->numberBetween(10, 200);
        $quantityRemaining = fake()->numberBetween(0, $quantityReceived);

        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'purchase_item_id' => PurchaseItem::factory(),
            'quantity_received' => $quantityReceived,
            'quantity_remaining' => $quantityRemaining,
            'unit_cost' => fake()->numberBetween(100, 10000), // in cents
            'batch_date' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }

    public function depleted(): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity_remaining' => 0,
        ]);
    }

    public function full(): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity_remaining' => $attributes['quantity_received'],
        ]);
    }
}
