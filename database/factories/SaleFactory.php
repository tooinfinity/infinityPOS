<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
final class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(SaleStatusEnum::class),
            'sale_date' => $this->faker->date(),
            'total_amount' => $this->faker->randomNumber(6),
            'paid_amount' => $this->faker->randomNumber(6),
            'change_amount' => $this->faker->randomNumber(2),
            'payment_status' => $this->faker->randomElement(PaymentStatusEnum::class),
            'note' => $this->faker->text(),
        ];
    }
}
