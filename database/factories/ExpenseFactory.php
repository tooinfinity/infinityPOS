<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RegisterSession;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
final class ExpenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'register_session_id' => fake()->optional()->randomElement([RegisterSession::factory(), null]),
            'expense_category' => fake()->randomElement(['utilities', 'supplies', 'maintenance', 'other']),
            'amount' => fake()->numberBetween(1000, 100000), // in cents
            'description' => fake()->sentence(),
            'expense_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'recorded_by' => User::factory(),
        ];
    }
}
