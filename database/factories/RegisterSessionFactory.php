<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegisterSession>
 */
final class RegisterSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openingBalance = fake()->numberBetween(5000, 50000); // in cents
        $expectedCash = fake()->numberBetween($openingBalance, $openingBalance + 100000);
        $actualCash = $expectedCash + fake()->numberBetween(-1000, 1000);
        $difference = $actualCash - $expectedCash;

        return [
            'cash_register_id' => CashRegister::factory(),
            'opened_by' => User::factory(),
            'closed_by' => fake()->optional()->randomElement([User::factory(), null]),
            'opening_time' => fake()->dateTimeBetween('-7 days', 'now'),
            'closing_time' => fake()->optional()->dateTimeBetween('now', '+1 day'),
            'opening_balance' => $openingBalance,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'notes' => fake()->optional()->sentence(),
            'status' => 'closed',
        ];
    }

    public function open(): self
    {
        return $this->state(fn (array $attributes): array => [
            'closed_by' => null,
            'closing_time' => null,
            'expected_cash' => null,
            'actual_cash' => null,
            'difference' => null,
            'status' => 'open',
        ]);
    }

    public function closed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'closed_by' => User::factory(),
            'closing_time' => fake()->dateTimeBetween('now', '+1 day'),
            'status' => 'closed',
        ]);
    }
}
