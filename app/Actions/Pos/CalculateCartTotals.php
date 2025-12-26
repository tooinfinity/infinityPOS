<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Data\Pos\PosCartTotalsData;
use App\Enums\TaxTypeEnum;
use App\Models\Product;

final readonly class CalculateCartTotals
{
    /**
     * @param  array<string, array{product_id:int,name:string,unit_price:int,quantity:int}>  $items
     */
    public function handle(array $items, int $cartDiscount = 0, int $taxOverride = 0): PosCartTotalsData
    {
        $subtotal = 0;
        $taxTotal = 0;

        $productIds = array_values(array_unique(array_map(
            static fn (array $line): int => $line['product_id'],
            $items,
        )));

        $products = Product::query()
            ->with('tax')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($items as $line) {
            $lineSubtotal = $line['unit_price'] * $line['quantity'];
            $subtotal += $lineSubtotal;

            /** @var Product|null $product */
            $product = $products->get($line['product_id']);
            $tax = $product?->tax;
            if ($tax === null) {
                continue;
            }

            if (! $tax->is_active) {
                continue;
            }

            $lineTax = match ($tax->tax_type) {
                TaxTypeEnum::PERCENTAGE => (int) round(($lineSubtotal * $tax->rate) / 100),
                TaxTypeEnum::FIXED => (int) ($tax->rate * $line['quantity']),
            };

            $taxTotal += $lineTax;
        }

        // Cart-level discount is applied before tax.
        $discountTotal = max(0, min($cartDiscount, $subtotal));

        $taxBase = $subtotal - $discountTotal;

        // If tax override is set, use it directly; otherwise calculate from products
        if ($taxOverride > 0) {
            $taxTotal = $taxOverride;
        } elseif ($subtotal > 0 && $discountTotal > 0) {
            // Recompute tax_total using discounted base proportionally per line.
            // For now, apply discount proportionally to each line's subtotal.
            $taxTotal = 0;
            foreach ($items as $line) {
                $lineSubtotal = $line['unit_price'] * $line['quantity'];
                $lineDiscount = (int) floor(($lineSubtotal / $subtotal) * $discountTotal);
                $lineTaxable = max(0, $lineSubtotal - $lineDiscount);

                /** @var Product|null $product */
                $product = $products->get($line['product_id']);
                $tax = $product?->tax;
                if ($tax === null) {
                    continue;
                }

                if (! $tax->is_active) {
                    continue;
                }

                $lineTax = match ($tax->tax_type) {
                    TaxTypeEnum::PERCENTAGE => (int) round(($lineTaxable * $tax->rate) / 100),
                    TaxTypeEnum::FIXED => (int) ($tax->rate * $line['quantity']),
                };

                $taxTotal += $lineTax;
            }
        }

        return new PosCartTotalsData(
            subtotal: $subtotal,
            discount_total: $discountTotal,
            tax_total: $taxTotal,
            total: $taxBase + $taxTotal,
        );
    }
}
