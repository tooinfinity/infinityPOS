<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleReturn;
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
        $quantity = $this->faker->randomNumber(2, 20);
        $price = $this->faker->randomNumber(2, 400);
        $cost = round($price * $this->faker->randomNumber(2, 9), 2);
        $discount = $this->faker->optional(3, 0)->randomNumber(2, $price * $quantity * 2);
        $taxBase = max(0, ($price * $quantity) - $discount);
        $taxAmount = round($taxBase * $this->faker->randomNumber(2, 2), 2);
        $total = round($taxBase + $taxAmount, 2);

        return [
            'sale_return_id' => SaleReturn::factory(),
            'product_id' => Product::factory(),
            'sale_item_id' => SaleItem::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'cost' => $cost,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }
}
