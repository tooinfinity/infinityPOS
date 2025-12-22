<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Models\Purchase;
use App\Models\PurchaseItem;

final readonly class CalculatePurchaseTotals
{
    public function handle(Purchase $purchase): Purchase
    {
        $items = $purchase->items;

        // @phpstan-ignore-next-line
        $subtotal = $items->sum(fn (PurchaseItem $item): int|float => ($item->cost * $item->quantity) - ($item->discount ?? 0));
        $tax = $items->sum('tax_amount');
        $total = $items->sum('total');

        $purchase->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        return $purchase;
    }
}
