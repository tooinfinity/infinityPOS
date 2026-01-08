<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoicePayment>
 */
final class InvoicePaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'payment_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'amount' => fake()->numberBetween(1000, 50000), // in cents
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer', 'check']),
            'reference_number' => fake()->optional()->bothify('PAY-####-????'),
            'notes' => fake()->optional()->sentence(),
            'recorded_by' => User::factory(),
        ];
    }
}
