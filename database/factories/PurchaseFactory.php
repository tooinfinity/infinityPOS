<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
final class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'reference_no' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(PurchaseStatusEnum::class),
            'purchase_date' => $this->faker->date(),
            'total_amount' => $this->faker->randomNumber(6),
            'paid_amount' => $this->faker->randomNumber(6),
            'payment_status' => $this->faker->randomElement(PaymentStatusEnum::class),
            'note' => $this->faker->text(),
            'document' => $this->faker->text(),
        ];
    }
}
