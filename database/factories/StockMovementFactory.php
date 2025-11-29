<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockMovementTypeEnum;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

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
        $qty = $this->faker->randomFloat(2, 1, 50);

        return [
            'product_id' => null,
            'store_id' => null,
            'quantity' => $qty,
            'type' => $type,
            'source_type' => null,
            'source_id' => null,
            'batch_number' => $this->faker->optional(0.2)->bothify('BATCH-#####'),
            'notes' => $this->faker->optional()->sentence(6),
            'user_id' => null,
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

    public function forSource(Model $model): self
    {
        return $this->for($model, 'source');
    }
}
