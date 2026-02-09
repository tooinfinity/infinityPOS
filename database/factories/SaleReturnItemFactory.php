<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
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
        $quantity = $this->faker->numberBetween(1, 100);
        $unitPrice = $this->faker->numberBetween(100, 1200);

        return [
            'sale_return_id' => SaleReturn::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }

    public function forSaleReturn(SaleReturn $saleReturn): self
    {
        return $this->state(fn (array $attributes): array => [
            'sale_return_id' => $saleReturn->id,
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

    public function withPricing(int $quantity, int $unitPrice): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ]);
    }
}
