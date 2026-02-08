<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
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
            'from_warehouse_id' => Warehouse::factory(),
            'to_warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(StockTransferStatusEnum::cases()),
            'note' => $this->faker->sentence(),
            'transfer_date' => now(),
        ];
    }

    public function fromWarehouse(Warehouse $warehouse): self
    {
        return $this->state(fn (array $attributes): array => [
            'from_warehouse_id' => $warehouse->id,
        ]);
    }

    public function toWarehouse(Warehouse $warehouse): self
    {
        return $this->state(fn (array $attributes): array => [
            'to_warehouse_id' => $warehouse->id,
        ]);
    }

    public function betweenWarehouses(Warehouse $from, Warehouse $to): self
    {
        return $this->state(fn (array $attributes): array => [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
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
            'status' => StockTransferStatusEnum::Pending,
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => StockTransferStatusEnum::Completed,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => StockTransferStatusEnum::Cancelled,
        ]);
    }

    public function today(): self
    {
        return $this->state(fn (array $attributes): array => [
            'transfer_date' => now(),
        ]);
    }

    public function withoutNote(): self
    {
        return $this->state(fn (array $attributes): array => [
            'note' => null,
        ]);
    }
}
