<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
final class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 100000); // in cents
        $discountAmount = fake()->numberBetween(0, (int) ($subtotal * 0.15));
        $totalAmount = $subtotal - $discountAmount;
        $paidAmount = fake()->numberBetween(0, $totalAmount);

        return [
            'store_id' => Store::factory(),
            'customer_id' => Customer::factory(),
            'invoice_number' => fake()->unique()->bothify('INV-####-????'),
            'invoice_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'payment_status' => $this->determinePaymentStatus($totalAmount, $paidAmount),
            'notes' => fake()->optional()->sentence(),
            'terms' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes): array => [
            'paid_amount' => $attributes['total_amount'],
            'payment_status' => 'paid',
        ]);
    }

    public function unpaid(): self
    {
        return $this->state(fn (array $attributes): array => [
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
        ]);
    }

    public function overdue(): self
    {
        return $this->state(fn (array $attributes): array => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'payment_status' => 'overdue',
        ]);
    }

    private function determinePaymentStatus(int $totalAmount, int $paidAmount): string
    {
        if ($paidAmount === 0) {
            return 'unpaid';
        }

        if ($paidAmount >= $totalAmount) {
            return 'paid';
        }

        return 'partial';
    }
}
