<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleReturnItem>
 */
final class SaleReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 10);
        $price = $this->faker->randomFloat(2, 5, 500);
        $cost = round($price * $this->faker->randomFloat(2, 0.5, 0.9), 2);
        $discount = $this->faker->optional(0.3, 0.0)->randomFloat(2, 0, $price * $quantity * 0.2);
        $taxBase = max(0, ($price * $quantity) - $discount);
        $taxAmount = round($taxBase * $this->faker->randomFloat(2, 0.0, 0.2), 2);
        $total = round($taxBase + $taxAmount, 2);

        return [
            'sale_return_id' => null,
            'product_id' => null,
            'sale_item_id' => null,
            'quantity' => $quantity,
            'price' => $price,
            'cost' => $cost,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
            'expiry_date' => null,
            'remaining_quantity' => $quantity,
        ];
    }
}
