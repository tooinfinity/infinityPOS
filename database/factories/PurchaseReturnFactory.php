<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReturnStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseReturn>
 */
final class PurchaseReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'return_date' => $this->faker->dateTimeThisYear(),
            'total_amount' => $this->faker->numberBetween(1000, 100000),
            'status' => $this->faker->randomElement(ReturnStatusEnum::cases()),
            'note' => $this->faker->sentence(),
        ];
    }

    public function forPurchase(Purchase $purchase): self
    {
        return $this->state(fn (array $attributes): array => [
            'purchase_id' => $purchase->id,
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
