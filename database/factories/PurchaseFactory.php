<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PurchaseStatusEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
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
        $subtotal = $this->faker->randomFloat(2, 10, 4000);
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
            'supplier_id' => null,
            'store_id' => null,
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
            'created_by' => null,
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

    /**
     * Attach purchase items and recompute totals accordingly.
     */
    public function withItems(int $count = 3): self
    {
        return $this->afterCreating(function (Purchase $purchase) use ($count): void {
            PurchaseItem::factory($count)->create(['purchase_id' => $purchase->id]);

            $subtotal = $purchase->items()->get()->reduce(
                fn (float $carry, PurchaseItem $item): float => $carry + max(0, ($item->cost * $item->quantity) - (float) ($item->discount ?? 0)),
                0.0
            );
            $discount = (float) $purchase->items()->sum('discount');
            $tax = (float) $purchase->items()->sum('tax_amount');
            $total = $subtotal - $discount + $tax;

            $purchase->forceFill([
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
        return $this->afterCreating(function (Purchase $purchase) use ($count): void {
            $remaining = (float) $purchase->paid;
            if ($remaining <= 0) {
                return;
            }

            $splits = $count > 0 ? $count : 1;
            $base = floor(($remaining / $splits) * 100) / 100; // 2 decimals
            $amounts = array_fill(0, $splits, $base);
            $amounts[array_key_first($amounts)] += $remaining - ($base * $splits);

            foreach ($amounts as $amt) {
                Payment::factory()->create([
                    'type' => 'purchase',
                    'related_id' => $purchase->id,
                    'amount' => max(0.01, $amt),
                ]);
            }
        });
    }
}
