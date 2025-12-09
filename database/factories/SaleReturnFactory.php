<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SaleReturnStatusEnum;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $subtotal = $this->faker->randomNumber(4, 4000);
        $discount = $this->faker->randomNumber(4, $subtotal * 2);
        $tax = $this->faker->randomNumber(4, ($subtotal - $discount) * 2);
        $total = round($subtotal - $discount + $tax, 2);
        $refunded = $this->faker->randomElement([
            0,
            round($total, 2),
            round($total * $this->faker->randomNumber(4, 9), 2),
        ]);

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'sale_id' => Sale::factory(),
            'client_id' => Client::factory(),
            'store_id' => Store::factory(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'refunded' => $refunded,
            'status' => $this->faker->randomElement([
                SaleReturnStatusEnum::PENDING->value,
                SaleReturnStatusEnum::COMPLETED->value,
                SaleReturnStatusEnum::CANCELLED->value,
            ]),
            'reason' => $this->faker->optional()->sentence(6),
            'notes' => $this->faker->optional()->sentence(8),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => SaleReturnStatusEnum::PENDING->value]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => SaleReturnStatusEnum::COMPLETED->value]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => SaleReturnStatusEnum::CANCELLED->value]);
    }
}
