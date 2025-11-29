<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
final class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 25);
        $cost = $this->faker->randomFloat(2, 1, 500);
        $discount = $this->faker->optional(0.3, 0.0)->randomFloat(2, 0, $cost * $quantity * 0.2);
        $taxBase = max(0, ($cost * $quantity) - $discount);
        $taxAmount = round($taxBase * $this->faker->randomFloat(2, 0.0, 0.2), 2);
        $total = round($taxBase + $taxAmount, 2);

        return [
            'purchase_id' => null,
            'product_id' => null,
            'quantity' => $quantity,
            'cost' => $cost,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
            'expiry_date' => null,
            'remaining_quantity' => $quantity,
        ];
    }

    public function ordered(): self
    {
        return $this->state(fn (array $attributes): array => [...$attributes, 'remaining_quantity' => 0]);
    }

    public function unordered(): self
    {
        return $this->state(fn (array $attributes): array => [...$attributes, 'remaining_quantity' => $attributes['quantity']]);
    }

    public function forPurchase(int $purchaseId): self
    {
        return $this->state(fn (array $attributes): array => [...$attributes, 'purchase_id' => $purchaseId]);
    }

    public function forProduct(int $productId): self
    {
        return $this->state(fn (array $attributes): array => [...$attributes, 'product_id' => $productId]);
    }
}
