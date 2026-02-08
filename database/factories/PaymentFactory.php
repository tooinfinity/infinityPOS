<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Payment>
 */
final class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_method_id' => PaymentMethod::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'payable_type' => Purchase::class,
            'payable_id' => Purchase::factory(),
            'amount' => $this->faker->numberBetween(1000, 10000),
            'payment_date' => $this->faker->date(),
            'note' => $this->faker->sentence(),
        ];
    }

    public function forPayable(Model $payable): self
    {
        return $this->state(fn (array $attributes): array => [
            'payable_type' => $payable::class,
            'payable_id' => $payable->getKey(),
        ]);
    }

    public function forPurchase(?Purchase $purchase = null): self
    {
        return $this->state(fn (array $attributes): array => [
            'payable_type' => Purchase::class,
            'payable_id' => $purchase->id ?? Purchase::factory(),
        ]);
    }

    public function forSale(?Sale $sale = null): self
    {
        return $this->state(fn (array $attributes): array => [
            'payable_type' => Sale::class,
            'payable_id' => $sale->id ?? Sale::factory(),
        ]);
    }

    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    public function forPaymentMethod(PaymentMethod $paymentMethod): self
    {
        return $this->state(fn (array $attributes): array => [
            'payment_method_id' => $paymentMethod->id,
        ]);
    }

    public function withAmount(int $amount): self
    {
        return $this->state(fn (array $attributes): array => [
            'amount' => $amount,
        ]);
    }

    public function today(): self
    {
        return $this->state(fn (array $attributes): array => [
            'payment_date' => now()->toDateString(),
        ]);
    }

    public function withoutNote(): self
    {
        return $this->state(fn (array $attributes): array => [
            'note' => null,
        ]);
    }
}
