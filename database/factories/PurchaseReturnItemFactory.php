<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
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
        $quantity = $this->faker->numberBetween(1, 100);
        $unitCost = $this->faker->numberBetween(100, 1000);

        return [
            'purchase_return_id' => PurchaseReturn::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'subtotal' => $quantity * $unitCost,
        ];
    }

    public function forPurchaseReturn(PurchaseReturn $purchaseReturn): self
    {
        return $this->state(fn (array $attributes): array => [
            'purchase_return_id' => $purchaseReturn->id,
        ]);
    }

    public function forProduct(Product $product): self
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
        ]);
    }

    public function forBatch(Batch $batch): self
    {
        return $this->state(fn (array $attributes): array => [
            'batch_id' => $batch->id,
        ]);
    }

    public function withoutBatch(): self
    {
        return $this->state(fn (array $attributes): array => [
            'batch_id' => null,
        ]);
    }

    public function withQuantity(int $quantity): self
    {
        return $this->state(function (array $attributes) use ($quantity): array {
            /** @var int $unitCost */
            $unitCost = $attributes['unit_cost'] ?? $this->faker->numberBetween(100, 1000);

            return [
                'quantity' => $quantity,
                'subtotal' => $quantity * $unitCost,
            ];
        });
    }

    public function withUnitCost(int $unitCost): self
    {
        return $this->state(function (array $attributes) use ($unitCost): array {
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 100);

            return [
                'unit_cost' => $unitCost,
                'subtotal' => $quantity * $unitCost,
            ];
        });
    }

    public function withPricing(int $quantity, int $unitCost): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'subtotal' => $quantity * $unitCost,
        ]);
    }
}
