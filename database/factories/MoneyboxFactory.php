<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MoneyboxTypeEnum;
use App\Models\Moneybox;
use App\Models\Store;
use App\Models\User;
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

        return [
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'type' => $type->value,
            'description' => $this->faker->optional()->sentence(),
            'balance' => $this->faker->randomNumber(2, 10000),
            'bank_name' => $type === MoneyboxTypeEnum::BANK_ACCOUNT ? $this->faker->optional()->company() : null,
            'account_number' => $type === MoneyboxTypeEnum::BANK_ACCOUNT ? $this->faker->optional()->iban() : null,
            'store_id' => Store::factory(),
            'created_by' => User::factory(),
            'updated_by' => null,
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

    public function bankAccount(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'type' => MoneyboxTypeEnum::BANK_ACCOUNT->value]);
    }
}
