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

    public function forCategory(ExpenseCategory $category): self
    {
        return $this->state(fn (array $attributes): array => [
            'expense_category_id' => $category->id,
        ]);
    }

    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    public function withAmount(int $amount): self
    {
        return $this->state(fn (array $attributes): array => [
            'amount' => $amount,
        ]);
    }

    public function withDocument(string $document): self
    {
        return $this->state(fn (array $attributes): array => [
            'document' => $document,
        ]);
    }

    public function today(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expense_date' => now()->toDateString(),
        ]);
    }

    public function thisMonth(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expense_date' => $this->faker->dateTimeBetween(
                now()->startOfMonth(),
                now()->endOfMonth()
            )->format('Y-m-d'),
        ]);
    }

    public function lastMonth(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expense_date' => $this->faker->dateTimeBetween(
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            )->format('Y-m-d'),
        ]);
    }
}
