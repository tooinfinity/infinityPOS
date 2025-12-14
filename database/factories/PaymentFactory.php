<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethodEnum;
// use App\Enums\PaymentTypeEnum;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Moneybox;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'reference' => mb_strtoupper(Str::random(12)),
            'related_type' => null,
            'amount' => $this->faker->randomNumber(2, 2000),
            'method' => $this->faker->randomElement(array_map(fn (PaymentMethodEnum $e) => $e->value, PaymentMethodEnum::cases())),
            'related_id' => null,
            'moneybox_id' => Moneybox::factory(),
            'notes' => $this->faker->optional()->sentence(6),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function methodCash(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'method' => PaymentMethodEnum::CASH->value]);
    }

    public function methodCard(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'method' => PaymentMethodEnum::CARD->value]);
    }

    public function methodTransfer(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'method' => PaymentMethodEnum::TRANSFER->value]);
    }

    public function forSale(int $saleId): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'related_type' => Sale::class,
            'related_id' => $saleId,
        ]);
    }

    public function forPurchase(int $purchaseId): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'related_type' => Purchase::class,
            'related_id' => $purchaseId,
        ]);
    }

    public function forExpense(int $expenseId): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'related_type' => Expense::class,
            'related_id' => $expenseId,
        ]);
    }

    public function forInvoice(int $invoiceId): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'related_type' => Invoice::class,
            'related_id' => $invoiceId,
        ]);
    }
}
