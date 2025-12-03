<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SaleReturnStatusEnum;
use App\Models\Payment;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
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
        $subtotal = $this->faker->randomFloat(2, 5, 1500);
        $discount = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        $tax = $this->faker->randomFloat(2, 0, ($subtotal - $discount) * 0.2);
        $total = round($subtotal - $discount + $tax, 2);
        $refunded = $this->faker->randomElement([
            0.0,
            round($total, 2),
            round($total * $this->faker->randomFloat(2, 0.1, 0.9), 2),
        ]);

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'sale_id' => null,
            'client_id' => null,
            'store_id' => null,
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
            'created_by' => null,
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

    public function withItems(int $count = 2): self
    {
        return $this->afterCreating(function (SaleReturn $return) use ($count): void {
            SaleReturnItem::factory($count)->create(['sale_return_id' => $return->id]);

            $subtotal = $return->items()->get()->reduce(
                fn (float $carry, SaleReturnItem $item): float => $carry + max(0, ($item->price * $item->quantity) - (float) ($item->discount ?? 0)),
                0.0
            );
            $discount = (float) $return->items()->sum('discount');
            $tax = (float) $return->items()->sum('tax_amount');
            $total = $subtotal - $discount + $tax;

            $return->forceFill([
                'subtotal' => (float) $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
            ])->save();
        });
    }

    public function withRefunds(int $count = 1): self
    {
        return $this->afterCreating(function (SaleReturn $return) use ($count): void {
            $remaining = (float) $return->refunded;
            if ($remaining <= 0) {
                return;
            }

            $splits = $count > 0 ? $count : 1;
            $base = floor(($remaining / $splits) * 100) / 100; // 2 decimals
            $amounts = array_fill(0, $splits, $base);
            $amounts[array_key_first($amounts)] += $remaining - ($base * $splits);

            foreach ($amounts as $amt) {
                Payment::factory()->create([
                    'type' => 'sale',
                    'related_id' => $return->id,
                    'amount' => max(0.01, $amt),
                ]);
            }
        });
    }
}
