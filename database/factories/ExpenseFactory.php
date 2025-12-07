<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\Store;
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
        $amount = $this->faker->numberBetween(1, 2000);

        return [
            'amount' => $amount,
            'description' => $this->faker->optional()->sentence(),
            'category_id' => Category::factory(),
            'store_id' => Store::factory(),
            'moneybox_id' => Moneybox::factory(),
            'created_by' => User::factory(),
            'updated_by' => null,
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
