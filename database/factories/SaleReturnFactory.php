<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReturnStatusEnum;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleReturn>
 */
final class SaleReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'return_date' => $this->faker->dateTimeThisYear(),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'status' => $this->faker->randomElement(ReturnStatusEnum::cases()),
            'note' => $this->faker->optional()->sentence(),
        ];
    }

    public function forSale(Sale $sale): self
    {
        return $this->state(fn (array $attributes): array => [
            'sale_id' => $sale->id,
        ]);
    }

    public function forWarehouse(Warehouse $warehouse): self
    {
        return $this->state(fn (array $attributes): array => [
            'warehouse_id' => $warehouse->id,
        ]);
    }

    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReturnStatusEnum::Pending,
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReturnStatusEnum::Completed,
        ]);
    }

    public function withTotalAmount(int $amount): self
    {
        return $this->state(fn (array $attributes): array => [
            'total_amount' => $amount,
        ]);
    }

    public function today(): self
    {
        return $this->state(fn (array $attributes): array => [
            'return_date' => now(),
        ]);
    }

    public function withoutNote(): self
    {
        return $this->state(fn (array $attributes): array => [
            'note' => null,
        ]);
    }
}
