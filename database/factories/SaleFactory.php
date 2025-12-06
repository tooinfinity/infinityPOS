<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Models\SaleItem;
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
        $subtotal = $this->faker->randomFloat(2, 10, 3000);
        $discount = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        $tax = $this->faker->randomFloat(2, 0, ($subtotal - $discount) * 0.2);
        $total = round($subtotal - $discount + $tax, 2);
        $paid = $this->faker->randomElement([
            0.0,
            round($total, 2),
            round($total * $this->faker->randomFloat(2, 0.1, 0.9), 2),
        ]);

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'client_id' => null,
            'store_id' => null,
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
            'created_by' => null,
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

    /**
     * Attach a number of items and recompute totals from items.
     */
    public function withItems(int $count = 3): self
    {
        return $this->afterCreating(function (Sale $sale) use ($count): void {
            SaleItem::factory($count)->create(['sale_id' => $sale->id]);

            $subtotal = $sale->items()->get()->reduce(fn (float $carry, SaleItem $item): float => $carry + max(0, ($item->price * $item->quantity) - (float) ($item->discount ?? 0)), 0.0);
            $discount = (float) $sale->items()->sum('discount');
            $tax = (float) $sale->items()->sum('tax_amount');
            $total = round($subtotal - $discount + $tax, 2);

            $sale->forceFill([
                'subtotal' => (float) $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
            ])->save();
        });
    }

    /**
     * Attach payments totaling the current paid amount.
     */
    public function withPayments(int $count = 1): self
    {
        return $this->afterCreating(function (Sale $sale) use ($count): void {
            $remaining = (float) $sale->paid;
            if ($remaining <= 0) {
                return;
            }

            $splits = $count > 0 ? $count : 1;
            $base = floor(($remaining / $splits) * 100) / 100; // 2 decimals
            $amounts = array_fill(0, $splits, $base);
            $amounts[array_key_first($amounts)] += $remaining - ($base * $splits);

            foreach ($amounts as $amt) {
                PaymentFactory::new()->create([
                    'type' => 'sale',
                    'related_id' => $sale->id,
                    'amount' => max(0.01, $amt),
                ]);
            }
        });
    }
}
