<?php

declare(strict_types=1);

namespace App\Traits;

use App\Actions\Pos\CalculateCartTotals;
use App\Data\Pos\PosCartData;
use App\Data\Pos\PosCartItemData;
use App\Services\Pos\CartService;

trait PresentsCart
{
    private function present(CartService $cart, CalculateCartTotals $totals): PosCartData
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $raw = $cart->getRaw();

        $items = [];
        foreach ($raw['items'] as $lineId => $line) {
            $items[] = new PosCartItemData(
                line_id: $lineId,
                product_id: $line['product_id'],
                name: $line['name'],
                unit_price: $line['unit_price'],
                quantity: $line['quantity'],
                line_subtotal: $line['unit_price'] * $line['quantity'],
            );
        }

        $totals = $totals->handle(
            $raw['items'],
            $raw['discount'],
            $raw['tax_override'] ?? 0
        );

        return new PosCartData(items: $items, totals: $totals);
    }
}
