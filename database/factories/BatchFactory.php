<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Batch>
 */
final class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'batch_number' => mb_strtoupper(Str::random(8)),
            'cost_amount' => $this->faker->randomNumber(6),
            'quantity' => $this->faker->randomNumber(2),
            'expires_at' => now()->addMonths(12),
        ];
    }
}
