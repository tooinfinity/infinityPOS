<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleReturn>
 */
final class SaleReturnFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => fake()->optional()->randomElement([Sale::factory(), null]),
            'invoice_id' => fake()->optional()->randomElement([Invoice::factory(), null]),
            'store_id' => Store::factory(),
            'customer_id' => fake()->optional()->randomElement([Customer::factory(), null]),
            'return_number' => fake()->unique()->bothify('RET-####-????'),
            'return_date' => fake()->dateTimeBetween('-7 days', 'now'),
            'total_amount' => fake()->numberBetween(500, 20000), // in cents
            'refund_method' => fake()->randomElement(['cash', 'card', 'store_credit']),
            'reason' => fake()->optional()->sentence(),
            'processed_by' => User::factory(),
        ];
    }

    public function fromSale(): self
    {
        return $this->state(fn (array $attributes): array => [
            'sale_id' => Sale::factory(),
            'invoice_id' => null,
        ]);
    }

    public function fromInvoice(): self
    {
        return $this->state(fn (array $attributes): array => [
            'sale_id' => null,
            'invoice_id' => Invoice::factory(),
        ]);
    }
}
