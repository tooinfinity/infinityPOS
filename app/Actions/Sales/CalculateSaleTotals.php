<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Models\Sale;
use App\Models\SaleItem;

final readonly class CalculateSaleTotals
{
    public function handle(Sale $sale): Sale
    {
        $items = $sale->items;

        // @phpstan-ignore-next-line
        $subtotal = $items->sum(fn (SaleItem $item): int|float => ($item->price * $item->quantity) - ($item->discount ?? 0));
        $tax = $items->sum('tax_amount');
        $total = $items->sum('total');

        $sale->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        return $sale;
    }
}
