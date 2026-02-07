<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReturnStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseReturn>
 */
final class PurchaseReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'return_date' => $this->faker->dateTimeThisYear(),
            'total_amount' => $this->faker->numberBetween(1000, 100000),
            'status' => $this->faker->randomElement(ReturnStatusEnum::cases()),
            'note' => $this->faker->sentence(),
        ];
    }
}
