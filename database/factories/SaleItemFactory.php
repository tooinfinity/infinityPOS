<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
final class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomNumber(2, 10);
        $price = $this->faker->randomNumber(2, 500);
        $cost = round($price * $this->faker->randomNumber(2, 9), 2);
        $discount = $this->faker->optional(3, 0)->randomNumber(2, $price * $quantity * 2);
        $taxBase = max(0, ($price * $quantity) - $discount);
        $taxAmount = round($taxBase * $this->faker->randomNumber(2, 2), 2);
        $total = round($taxBase + $taxAmount, 2);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'cost' => $cost,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
            'expiry_date' => null,
        ];
    }
}
