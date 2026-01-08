<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalePayment>
 */
final class SalePaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'payment_method' => fake()->randomElement(['cash', 'card']),
            'amount' => fake()->numberBetween(1000, 50000), // in cents
            'reference_number' => fake()->optional()->bothify('REF-####-????'),
            'created_at' => now(),
        ];
    }
}
