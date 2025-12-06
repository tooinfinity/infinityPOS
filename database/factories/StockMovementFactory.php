<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockMovementTypeEnum;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
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
        $type = $this->faker->randomElement(array_map(fn (StockMovementTypeEnum $e) => $e->value, StockMovementTypeEnum::cases()));
        $qty = $this->faker->randomNumber(2, 50);

        return [
            'product_id' => Product::factory(),
            'store_id' => Store::factory(),
            'quantity' => $qty,
            'type' => $type,
            'reference' => $this->faker->optional()->bothify('REF-#####'),
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
            'notes' => $this->faker->optional()->sentence(6),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function incoming(): self
    {
        return $this->state(function (array $attrs): array {
            $attrs['type'] = $this->faker->randomElement([
                StockMovementTypeEnum::PURCHASE->value,
                StockMovementTypeEnum::SALE_RETURN->value,
            ]);

            return $attrs;
        });
    }

    public function outgoing(): self
    {
        return $this->state(function (array $attrs): array {
            $attrs['type'] = $this->faker->randomElement([
                StockMovementTypeEnum::SALE->value,
                StockMovementTypeEnum::PURCHASE_RETURN->value,
            ]);

            return $attrs;
        });
    }

    public function withReference(string $reference): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'reference' => $reference]);
    }
}
