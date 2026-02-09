<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
final class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => $this->faker->unique()->word(),
            'is_active' => true,
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function cash(): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Cash',
            'code' => 'cash',
        ]);
    }

    public function card(): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Card',
            'code' => 'card',
        ]);
    }

    public function bankTransfer(): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Bank Transfer',
            'code' => 'bank_transfer',
        ]);
    }
}
