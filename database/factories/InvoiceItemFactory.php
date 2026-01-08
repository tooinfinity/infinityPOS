<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
final class InvoiceItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 50);
        $unitPrice = fake()->numberBetween(500, 20000); // in cents
        $unitCost = fake()->numberBetween(200, (int) ($unitPrice * 0.7)); // in cents
        $subtotal = $quantity * $unitPrice;
        $profit = ($unitPrice - $unitCost) * $quantity;

        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'subtotal' => $subtotal,
            'profit' => $profit,
        ];
    }
}
