<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $total = $this->faker->randomNumber(4, 1500);
        $refunded = $this->faker->randomElement([
            0,
            round($total, 2),
            round($total * $this->faker->randomNumber(2, 9), 2),
        ]);

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'purchase_id' => Purchase::factory(),
            'supplier_id' => Supplier::factory(),
            'store_id' => Store::factory(),
            'subtotal' => $this->faker->randomNumber(4, 1500),
            'discount' => $this->faker->optional()->randomNumber(4, 1500),
            'tax' => $this->faker->optional()->randomNumber(4, 1500),
            'total' => $total,
            'refunded' => $refunded,
            'status' => $this->faker->randomElement([
                PurchaseReturnStatusEnum::PENDING->value,
                PurchaseReturnStatusEnum::COMPLETED->value,
                PurchaseReturnStatusEnum::CANCELLED->value,
            ]),
            'reason' => $this->faker->optional()->sentence(6),
            'notes' => $this->faker->optional()->sentence(8),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => PurchaseReturnStatusEnum::PENDING->value]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => PurchaseReturnStatusEnum::COMPLETED->value]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $a): array => [...$a, 'status' => PurchaseReturnStatusEnum::CANCELLED->value]);
    }
}
