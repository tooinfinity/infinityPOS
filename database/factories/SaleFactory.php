<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
final class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(SaleStatusEnum::cases()),
            'sale_date' => $this->faker->date(),
            'total_amount' => $this->faker->randomNumber(6),
            'paid_amount' => $this->faker->randomNumber(6),
            'change_amount' => $this->faker->randomNumber(2),
            'payment_status' => $this->faker->randomElement(PaymentStatusEnum::cases()),
            'note' => $this->faker->text(),
        ];
    }

    public function forCustomer(Customer $customer): self
    {
        return $this->state(fn (array $attributes): array => [
            'customer_id' => $customer->id,
        ]);
    }

    public function withoutCustomer(): self
    {
        return $this->state(fn (array $attributes): array => [
            'customer_id' => null,
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
            'status' => SaleStatusEnum::Pending,
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SaleStatusEnum::Completed,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SaleStatusEnum::Cancelled,
        ]);
    }

    public function unpaid(): self
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => PaymentStatusEnum::Unpaid,
            'paid_amount' => 0,
            'change_amount' => 0,
        ]);
    }

    public function partiallyPaid(): self
    {
        return $this->state(function (array $attributes): array {
            /** @var int $totalAmount */
            $totalAmount = $attributes['total_amount'] ?? $this->faker->randomNumber(6);
            $paidAmount = max(1, (int) ($totalAmount * $this->faker->randomFloat(2, 0.1, 0.9)));

            return [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => 0,
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
                'change_amount' => 0,
                'payment_status' => PaymentStatusEnum::Paid,
            ];
        });
    }

    public function paidWithChange(int $changeAmount): self
    {
        return $this->state(function (array $attributes) use ($changeAmount): array {
            /** @var int $totalAmount */
            $totalAmount = $attributes['total_amount'] ?? $this->faker->randomNumber(6);

            return [
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount + $changeAmount,
                'change_amount' => $changeAmount,
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
            'sale_date' => now()->toDateString(),
        ]);
    }

    public function withoutNote(): self
    {
        return $this->state(fn (array $attributes): array => [
            'note' => null,
        ]);
    }
}
