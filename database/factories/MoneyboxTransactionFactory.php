<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
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
        $before = $this->faker->randomFloat(2, 0, 20000);
        $type = $this->faker->randomElement(MoneyboxTransactionTypeEnum::cases());
        $after = match ($type) {
            MoneyboxTransactionTypeEnum::IN => $before + $amount,
            MoneyboxTransactionTypeEnum::OUT => max(0, $before - $amount),
            MoneyboxTransactionTypeEnum::TRANSFER => $before, // balance may remain same on source if mirrored separately
        };

        return [
            'moneybox_id' => Moneybox::factory(),
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'transfer_to_moneybox_id' => null,
            'transactionable_type' => $this->faker->randomElement([
                Payment::class,
                Expense::class,
            ]),
            'transactionable_id' => 1, // recommend overriding in tests when linking to real models
            'reference' => $this->faker->optional()->bothify('TRX-#####'),
            'notes' => $this->faker->optional()->sentence(),
            'user_id' => null,
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
        return $this->state(fn (array $attrs): array => [...$attrs, 'transfer_to_moneybox_id' => $moneybox->id]);
    }

    public function byUser(int $userId): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'user_id' => $userId]);
    }
}
