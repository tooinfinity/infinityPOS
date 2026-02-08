<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
final class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $previousQuantity = $this->faker->numberBetween(0, 100);
        $quantity = $this->faker->numberBetween(1, 50);
        $currentQuantity = $previousQuantity + $quantity;

        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(StockMovementTypeEnum::cases()),
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'current_quantity' => $currentQuantity,
            'reference_type' => $this->faker->randomElement(['Sale', 'Purchase', 'Sale_Return', 'Purchase_Return']),
            'reference_id' => $this->faker->numberBetween(1, 100),
            'note' => $this->faker->sentence(),
            'created_at' => now(),
        ];
    }

    public function forWarehouse(Warehouse $warehouse): self
    {
        return $this->state(fn (array $attributes): array => [
            'warehouse_id' => $warehouse->id,
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

    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    public function stockIn(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $previousQuantity */
            $previousQuantity = $attributes['previous_quantity'] ?? $this->faker->numberBetween(0, 100);
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 50);

            return [
                'type' => StockMovementTypeEnum::In,
                'previous_quantity' => $previousQuantity,
                'current_quantity' => $previousQuantity + $quantity,
            ];
        });
    }

    public function stockOut(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $quantity */
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 50);
            /** @var int $previousQuantity */
            $previousQuantity = $attributes['previous_quantity'] ?? $this->faker->numberBetween($quantity, 100);

            return [
                'type' => StockMovementTypeEnum::Out,
                'previous_quantity' => $previousQuantity,
                'current_quantity' => $previousQuantity - $quantity,
            ];
        });
    }

    public function adjustment(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => StockMovementTypeEnum::Adjustment,
        ]);
    }

    public function transfer(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => StockMovementTypeEnum::Transfer,
        ]);
    }

    public function forSale(Sale $sale): self
    {
        return $this->state(fn (array $attributes): array => [
            'reference_type' => 'Sale',
            'reference_id' => $sale->id,
        ]);
    }

    public function forPurchase(Purchase $purchase): self
    {
        return $this->state(fn (array $attributes): array => [
            'reference_type' => 'Purchase',
            'reference_id' => $purchase->id,
        ]);
    }

    public function withQuantities(int $previousQuantity, int $quantity, int $currentQuantity): self
    {
        return $this->state(fn (array $attributes): array => [
            'previous_quantity' => $previousQuantity,
            'quantity' => $quantity,
            'current_quantity' => $currentQuantity,
        ]);
    }
}
