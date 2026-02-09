<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
final class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(PurchaseStatusEnum::cases()),
            'purchase_date' => $this->faker->date(),
            'total_amount' => $this->faker->randomNumber(6),
            'paid_amount' => $this->faker->randomNumber(6),
            'payment_status' => $this->faker->randomElement(PaymentStatusEnum::cases()),
            'note' => $this->faker->text(),
            'document' => $this->faker->text(),
        ];
    }

    public function forSupplier(Supplier $supplier): self
    {
        return $this->state(fn (array $attributes): array => [
            'supplier_id' => $supplier->id,
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
            'status' => PurchaseStatusEnum::Pending,
        ]);
    }

    public function ordered(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PurchaseStatusEnum::Ordered,
        ]);
    }

    public function received(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PurchaseStatusEnum::Received,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PurchaseStatusEnum::Cancelled,
        ]);
    }

    public function unpaid(): self
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => PaymentStatusEnum::Unpaid,
            'paid_amount' => 0,
        ]);
    }

    public function partiallyPaid(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $totalAmount */
            $totalAmount = $attributes['total_amount'] ?? $this->faker->randomNumber(6);
            $paidAmount = (int) ($totalAmount * $this->faker->randomFloat(2, 0.1, 0.9));

            return [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_status' => PaymentStatusEnum::Partial,
            ];
        });
    }

    public function paid(): self
    {
        return $this->state(function (array $attributes): array {
            $totalAmount = $attributes['total_amount'] ?? $this->faker->randomNumber(6);

            return [
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'payment_status' => PaymentStatusEnum::Paid,
            ];
        });
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
            'purchase_date' => now()->toDateString(),
        ]);
    }

    public function withoutDocument(): self
    {
        return $this->state(fn (array $attributes): array => [
            'document' => null,
        ]);
    }

    public function withoutNote(): self
    {
        return $this->state(fn (array $attributes): array => [
            'note' => null,
        ]);
    }
}
