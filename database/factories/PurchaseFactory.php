<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $subtotal = $this->faker->randomNumber(4, 4000);
        $discount = $this->faker->randomNumber(4, $subtotal * 2);
        $tax = $this->faker->randomNumber(4, ($subtotal - $discount) * 2);
        $total = round($subtotal - $discount + $tax, 2);
        $paid = $this->faker->randomElement([
            0,
            round($total, 2),
            round($total * $this->faker->randomNumber(2, 9), 2),
        ]);

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'supplier_id' => Supplier::factory(),
            'store_id' => Store::factory(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'paid' => $paid,
            'status' => $this->faker->randomElement([
                PurchaseStatusEnum::PENDING->value,
                PurchaseStatusEnum::RECEIVED->value,
                PurchaseStatusEnum::CANCELLED->value,
            ]),
            'notes' => $this->faker->optional()->sentence(8),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => PurchaseStatusEnum::PENDING->value]);
    }

    public function received(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => PurchaseStatusEnum::RECEIVED->value]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => PurchaseStatusEnum::CANCELLED->value]);
    }
}
