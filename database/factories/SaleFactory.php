<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SaleStatusEnum;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $subtotal = $this->faker->randomNumber(2, 3000);
        $discount = $this->faker->randomNumber(2, $subtotal * 2);
        $tax = $this->faker->randomNumber(2, ($subtotal - $discount) * 2);
        $total = round($subtotal - $discount + $tax, 2);
        $paid = $this->faker->randomElement([
            0,
            round($total, 2),
            round($total * $this->faker->randomNumber(2, 9), 2),
        ]);

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'client_id' => Client::factory(),
            'store_id' => Store::factory(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'paid' => $paid,
            'status' => $this->faker->randomElement([
                SaleStatusEnum::COMPLETED->value,
                SaleStatusEnum::CANCELLED->value,
            ]),
            'notes' => $this->faker->optional()->sentence(8),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    /**
     * Mark the sale as completed.
     */
    public function completed(): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'status' => SaleStatusEnum::COMPLETED->value,
        ]);
    }

    /**
     * Mark the sale as cancelled.
     */
    public function cancelled(): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'status' => SaleStatusEnum::CANCELLED->value,
        ]);
    }
}
