<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
final class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_category_id' => ExpenseCategory::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'amount' => $this->faker->numberBetween(1000, 100000),
            'expense_date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'document' => null,
        ];
    }
}
