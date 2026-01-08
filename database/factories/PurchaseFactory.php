<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethodEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
final class PurchaseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCost = fake()->numberBetween(10000, 500000); // in cents
        $paidAmount = fake()->numberBetween(0, $totalCost);

        return [
            'store_id' => Store::factory(),
            'supplier_id' => Supplier::factory(),
            'reference_number' => fake()->unique()->bothify('PO-####-????'),
            'invoice_number' => fake()->optional()->bothify('INV-####'),
            'purchase_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'total_cost' => $totalCost,
            'paid_amount' => $paidAmount,
            'payment_status' => fake()->randomElement(PurchaseStatusEnum::values()),
            'payment_method' => fake()->randomElement(PaymentMethodEnum::values()),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'paid_amount' => $attributes['total_cost'],
            'payment_status' => PurchaseStatusEnum::COMPLETED->value,
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes): array => [
            'paid_amount' => 0,
            'payment_status' => PurchaseStatusEnum::PENDING->value,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => PurchaseStatusEnum::CANCELLED->value,
        ]);
    }
}
