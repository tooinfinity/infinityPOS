<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleReturnItem>
 */
final class SaleReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_return_id' => SaleReturn::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_price' => $this->faker->numberBetween(1, 100),
            'subtotal' => $this->faker->numberBetween(1, 10000),
        ];
    }
}
