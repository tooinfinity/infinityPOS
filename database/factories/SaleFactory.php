<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\RegisterSession;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
final class SaleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 50000); // in cents
        $discountAmount = fake()->numberBetween(0, (int) ($subtotal * 0.2));
        $totalAmount = $subtotal - $discountAmount;
        $amountPaid = $totalAmount + fake()->numberBetween(0, 5000);
        $changeGiven = $amountPaid - $totalAmount;

        return [
            'store_id' => Store::factory(),
            'customer_id' => fake()->optional()->randomElement([Customer::factory(), null]),
            'register_session_id' => RegisterSession::factory(),
            'invoice_number' => fake()->unique()->bothify('SALE-####-????'),
            'sale_date' => fake()->dateTimeBetween('-7 days', 'now'),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'payment_method' => fake()->randomElement(['cash', 'card', 'split']),
            'amount_paid' => $amountPaid,
            'change_given' => $changeGiven,
            'status' => 'completed',
            'notes' => fake()->optional()->sentence(),
            'cashier_id' => User::factory(),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'pending',
        ]);
    }

    public function returned(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'returned',
        ]);
    }
}
