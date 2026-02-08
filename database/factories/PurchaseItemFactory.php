<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
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
        $quantity = $this->faker->numberBetween(1, 100);
        $unitCost = $this->faker->numberBetween(100, 1000);

        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => $quantity,
            'received_quantity' => $quantity,
            'unit_cost' => $unitCost,
            'subtotal' => $quantity * $unitCost,
            'expires_at' => now()->addMonths(12),
        ];
    }

    public function forPurchase(Purchase $purchase): self
    {
        return $this->state(fn (array $attributes): array => [
            'purchase_id' => $purchase->id,
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
                'received_quantity' => $quantity,
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
            'received_quantity' => $quantity,
            'unit_cost' => $unitCost,
            'subtotal' => $quantity * $unitCost,
        ]);
    }

    public function partiallyReceived(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(10, 100);

            return [
                'quantity' => $quantity,
                'received_quantity' => $this->faker->numberBetween(1, $quantity - 1),
            ];
        });
    }

    public function fullyReceived(): self
    {
        return $this->state(function (array $attributes): array {
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 100);

            return [
                'quantity' => $quantity,
                'received_quantity' => $quantity,
            ];
        });
    }

    public function notReceived(): self
    {
        return $this->state(fn (array $attributes): array => [
            'received_quantity' => 0,
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    public function neverExpires(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => null,
        ]);
    }
}
