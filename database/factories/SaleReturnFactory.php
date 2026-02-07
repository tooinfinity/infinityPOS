<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SaleReturnStatusEnum;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleReturn>
 */
final class SaleReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'return_date' => $this->faker->dateTimeThisYear(),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'status' => $this->faker->randomElement(SaleReturnStatusEnum::class),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
