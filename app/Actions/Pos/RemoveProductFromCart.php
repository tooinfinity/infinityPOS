<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Services\Pos\CartService;

final readonly class RemoveProductFromCart
{
    public function __construct(private CartService $cart) {}

    public function handle(string $lineId): void
    {
        $sale = $this->cart->getDraftSale();
        if (! $sale instanceof \App\Models\Sale) {
            return;
        }

        if (! str_starts_with($lineId, 'item_')) {
            return;
        }

        $saleItemId = (int) str_replace('item_', '', $lineId);

        $sale->items()->whereKey($saleItemId)->delete();
    }
}
