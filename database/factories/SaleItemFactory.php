<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
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
        $quantity = $this->faker->numberBetween(1, 100);
        $unitCost = $this->faker->numberBetween(100, 1000);
        $unitPrice = $unitCost + $this->faker->numberBetween(10, 200);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'subtotal' => $quantity * $unitPrice,
        ];
    }

    public function forSale(Sale $sale): self
    {
        return $this->state(fn (array $attributes): array => [
            'sale_id' => $sale->id,
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
            /** @var int $unitPrice */
            $unitPrice = $attributes['unit_price'] ?? $this->faker->numberBetween(100, 1200);

            return [
                'quantity' => $quantity,
                'subtotal' => $quantity * $unitPrice,
            ];
        });
    }

    public function withUnitPrice(int $unitPrice): self
    {
        return $this->state(function (array $attributes) use ($unitPrice): array {
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 100);

            return [
                'unit_price' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
            ];
        });
    }

    public function withPricing(int $quantity, int $unitPrice, int $unitCost): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'subtotal' => $quantity * $unitPrice,
        ]);
    }

    public function withMargin(int $unitCost, int $marginPercent): self
    {
        $unitPrice = (int) ($unitCost * (1 + $marginPercent / 100));

        return $this->state(function (array $attributes) use ($unitCost, $unitPrice): array {
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 100);

            return [
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
            ];
        });
    }

    public function atCost(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $unitCost */
            $unitCost = $attributes['unit_cost'] ?? $this->faker->numberBetween(100, 1000);
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 100);

            return [
                'unit_price' => $unitCost,
                'subtotal' => $quantity * $unitCost,
            ];
        });
    }

    public function atLoss(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $unitCost */
            $unitCost = $attributes['unit_cost'] ?? $this->faker->numberBetween(100, 1000);
            $unitPrice = max(1, (int) ($unitCost * $this->faker->randomFloat(2, 0.5, 0.9)));
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 100);

            return [
                'unit_price' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
            ];
        });
    }
}
