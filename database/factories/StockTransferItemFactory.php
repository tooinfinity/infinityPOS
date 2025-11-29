<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransferItem>
 */
final class StockTransferItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_transfer_id' => StockTransfer::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomFloat(2, 1, 50),
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
        ];
    }
}
