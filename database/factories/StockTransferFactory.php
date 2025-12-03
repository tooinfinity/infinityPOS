<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransfer>
 */
final class StockTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => mb_strtoupper($this->faker->bothify('TR-#####')),
            'from_store_id' => Store::factory(),
            'to_store_id' => Store::factory(),
            'status' => $this->faker->randomElement([
                StockTransferStatusEnum::PENDING->value,
                StockTransferStatusEnum::COMPLETED->value,
                StockTransferStatusEnum::CANCELLED->value,
            ]),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'status' => StockTransferStatusEnum::PENDING->value,
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'status' => StockTransferStatusEnum::COMPLETED->value,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'status' => StockTransferStatusEnum::CANCELLED->value,
        ]);
    }
}
