<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Expense;
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
        $amount = $this->faker->randomFloat(2, 1, 2000);

        return [
            'amount' => $amount,
            'description' => $this->faker->optional()->sentence(),
            'category_id' => null,
            'store_id' => null,
            'user_id' => null,
            'moneybox_id' => null,
        ];
    }

    /**
     * Set the expense category.
     */
    public function category(int $categoryId): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'category_id' => $categoryId]);
    }
}
