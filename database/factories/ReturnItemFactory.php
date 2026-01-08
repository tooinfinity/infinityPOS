<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnItem>
 */
final class ReturnItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->numberBetween(500, 20000); // in cents
        $unitCost = fake()->numberBetween(200, (int) ($unitPrice * 0.7)); // in cents
        $subtotal = $quantity * $unitPrice;

        return [
            'return_id' => SaleReturn::factory(),
            'sale_item_id' => fake()->optional()->randomElement([SaleItem::factory(), null]),
            'invoice_item_id' => fake()->optional()->randomElement([InvoiceItem::factory(), null]),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'subtotal' => $subtotal,
        ];
    }
}
