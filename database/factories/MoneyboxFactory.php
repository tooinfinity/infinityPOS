<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MoneyboxTypeEnum;
use App\Models\Moneybox;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Moneybox>
 */
final class MoneyboxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(MoneyboxTypeEnum::cases());
        $opening = $this->faker->randomFloat(2, 0, 10000);
        $delta = $this->faker->randomFloat(2, -2000, 4000);

        return [
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'type' => $type,
            'description' => $this->faker->optional()->sentence(),
            'opening_balance' => $opening,
            'current_balance' => max(0, $opening + $delta),
            'bank_name' => $type === MoneyboxTypeEnum::BANK_ACCOUNT ? $this->faker->optional()->company() : null,
            'account_number' => $type === MoneyboxTypeEnum::BANK_ACCOUNT ? $this->faker->optional()->bankAccountNumber() : null,
            'iban' => $type === MoneyboxTypeEnum::BANK_ACCOUNT ? $this->faker->optional()->iban() : null,
            'store_id' => null,
            'user_id' => null,
            'is_active' => $this->faker->boolean(95),
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'is_active' => true]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'is_active' => false]);
    }

    public function forStore(int $storeId): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'store_id' => $storeId]);
    }

    public function forUser(int $userId): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'user_id' => $userId]);
    }

    public function bankAccount(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'type' => MoneyboxTypeEnum::BANK_ACCOUNT]);
    }
}
