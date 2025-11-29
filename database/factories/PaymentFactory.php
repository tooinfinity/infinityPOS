<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethodEnum;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
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
            'payable_type' => null,
            'payable_id' => null,
            'amount' => $this->faker->randomFloat(2, 5, 2000),
            'method' => $this->faker->randomElement(array_map(fn (PaymentMethodEnum $e) => $e->value, PaymentMethodEnum::cases())),
            'moneybox_id' => null,
            'notes' => $this->faker->optional()->sentence(6),
            'user_id' => null,
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

    public function forPayable(Model $model): self
    {
        return $this->for($model, 'payable');
    }

    public function forSale(Sale $sale): self
    {
        return $this->for($sale, 'payable');
    }

    public function forInvoice(Invoice $invoice): self
    {
        return $this->for($invoice, 'payable');
    }

    public function forPurchase(Purchase $purchase): self
    {
        return $this->for($purchase, 'payable');
    }
}
