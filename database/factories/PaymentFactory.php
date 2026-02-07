<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
final class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_method_id' => PaymentMethod::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'payable_type' => Purchase::class,
            'payable_id' => $this->faker->numberBetween(1, 100),
            'amount' => $this->faker->numberBetween(1000, 10000),
            'payment_date' => $this->faker->date(),
            'note' => $this->faker->sentence(),
        ];
    }
}
