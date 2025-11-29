<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Payment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
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
        $total = $this->faker->randomFloat(2, 5, 1500);
        $refunded = $this->faker->randomElement([
            0.0,
            round($total, 2),
            round($total * $this->faker->randomFloat(2, 0.1, 0.9), 2),
        ]);

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'purchase_id' => null,
            'supplier_id' => null,
            'store_id' => null,
            'total' => $total,
            'refunded' => $refunded,
            'status' => $this->faker->randomElement([
                PurchaseReturnStatusEnum::PENDING->value,
                PurchaseReturnStatusEnum::COMPLETED->value,
                PurchaseReturnStatusEnum::CANCELLED->value,
            ]),
            'reason' => $this->faker->optional()->sentence(6),
            'notes' => $this->faker->optional()->sentence(8),
            'user_id' => null,
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

    public function withItems(int $count = 2): self
    {
        return $this->afterCreating(function (PurchaseReturn $return) use ($count): void {
            PurchaseReturnItem::factory($count)->create(['purchase_return_id' => $return->id]);

            $total = (float) $return->items()->sum('total');
            $return->forceFill(['total' => $total])->save();
        });
    }

    public function withRefunds(int $count = 1): self
    {
        return $this->afterCreating(function (PurchaseReturn $return) use ($count): void {
            $remaining = (float) $return->refunded;
            if ($remaining <= 0) {
                return;
            }

            $splits = $count > 0 ? $count : 1;
            $base = floor(($remaining / $splits) * 100) / 100; // 2 decimals
            $amounts = array_fill(0, $splits, $base);
            $amounts[array_key_first($amounts)] += $remaining - ($base * $splits);

            foreach ($amounts as $amt) {
                Payment::factory()->for($return, 'payable')->create(['amount' => max(0.01, $amt)]);
            }
        });
    }
}
