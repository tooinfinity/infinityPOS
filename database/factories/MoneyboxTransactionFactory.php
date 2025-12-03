<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MoneyboxTransaction>
 */
final class MoneyboxTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 1, 5000);
        $type = $this->faker->randomElement(MoneyboxTransactionTypeEnum::cases());
        $balanceAfter = $this->faker->randomFloat(2, 0, 20000);

        return [
            'moneybox_id' => Moneybox::factory(),
            'type' => $type->value,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'reference' => $this->faker->optional()->bothify('TRX-#####'),
            'notes' => $this->faker->optional()->sentence(),
            'payment_id' => null,
            'expense_id' => null,
            'transfer_to_id' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function incoming(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'type' => MoneyboxTransactionTypeEnum::IN->value]);
    }

    public function outgoing(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'type' => MoneyboxTransactionTypeEnum::OUT->value]);
    }

    public function transfer(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'type' => MoneyboxTransactionTypeEnum::TRANSFER->value]);
    }

    public function forMoneybox(Moneybox $moneybox): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'moneybox_id' => $moneybox->id]);
    }

    public function forTransferTo(Moneybox $moneybox): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'transfer_to_id' => $moneybox->id]);
    }

    public function forPayment(int $paymentId): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'payment_id' => $paymentId]);
    }

    public function forExpense(int $expenseId): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'expense_id' => $expenseId]);
    }
}
