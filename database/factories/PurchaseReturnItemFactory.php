<?php

declare(strict_types=1);

namespace Database\Factories;

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
        $quantity = $this->faker->randomFloat(2, 1, 20);
        $cost = $this->faker->randomFloat(2, 1, 400);
        $total = round($cost * $quantity, 2);

        return [
            'purchase_return_id' => null,
            'product_id' => null,
            'purchase_item_id' => null,
            'quantity' => $quantity,
            'cost' => $cost,
            'total' => $total,
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
        ];
    }
}
