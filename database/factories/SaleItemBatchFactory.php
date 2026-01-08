<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InventoryBatch;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItemBatch>
 */
final class SaleItemBatchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_item_id' => SaleItem::factory(),
            'inventory_batch_id' => InventoryBatch::factory(),
            'quantity_used' => fake()->numberBetween(1, 20),
            'unit_cost' => fake()->numberBetween(100, 10000), // in cents
            'created_at' => now(),
        ];
    }
}
