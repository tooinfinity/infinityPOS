<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RegisterSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashTransaction>
 */
final class CashTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'register_session_id' => RegisterSession::factory(),
            'transaction_type' => fake()->randomElement(['sale', 'expense', 'withdrawal', 'deposit', 'opening', 'closing']),
            'amount' => fake()->numberBetween(-50000, 50000), // in cents (can be negative)
            'reference_type' => fake()->optional()->randomElement([\App\Models\Sale::class, \App\Models\Expense::class, \App\Models\SaleReturn::class]),
            'reference_id' => fake()->optional()->numberBetween(1, 100),
            'description' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
            'created_at' => now(),
        ];
    }

    public function deposit(): self
    {
        return $this->state(fn (array $attributes): array => [
            'transaction_type' => 'deposit',
            'amount' => fake()->numberBetween(1000, 50000),
        ]);
    }

    public function withdrawal(): self
    {
        return $this->state(fn (array $attributes): array => [
            'transaction_type' => 'withdrawal',
            'amount' => fake()->numberBetween(-50000, -1000),
        ]);
    }
}
