<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseReturnItem>
 */
final class PurchaseReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomNumber(2, 20);
        $cost = $this->faker->randomNumber(2, 400);
        $total = round($cost * $quantity, 2);

        return [
            'purchase_return_id' => PurchaseReturn::factory(),
            'product_id' => Product::factory(),
            'purchase_item_id' => PurchaseItem::factory(),
            'quantity' => $quantity,
            'cost' => $cost,
            'total' => $total,
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
        ];
    }
}
