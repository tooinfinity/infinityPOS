<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
final class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(StockMovementTypeEnum::class),
            'quantity' => $this->faker->numberBetween(1, 100),
            'previous_quantity' => $this->faker->numberBetween(1, 100),
            'current_quantity' => $this->faker->numberBetween(1, 100),
            'reference_type' => $this->faker->randomElement(['Sale', 'Purchase', 'Sale_Return', 'Purchase_Return']),
            'reference_id' => $this->faker->numberBetween(1, 100),
            'note' => $this->faker->sentence(),
            'created_at' => now(),
        ];
    }
}
