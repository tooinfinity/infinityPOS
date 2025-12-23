<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Data\Pos\UpdateCartItemData;
use App\Models\SaleItem;
use App\Services\Pos\CartService;
use DomainException;
use Throwable;

final readonly class UpdateCartItem
{
    public function __construct(private CartService $cart) {}

    /**
     * @throws Throwable
     */
    public function handle(string $lineId, UpdateCartItemData $data): void
    {
        $sale = $this->cart->getDraftSale();
        throw_unless($sale, DomainException::class, 'Cart is empty');

        throw_unless(str_starts_with($lineId, 'item_'), DomainException::class, 'Cart line not found');

        $saleItemId = (int) str_replace('item_', '', $lineId);

        /** @var SaleItem|null $item */
        $item = $sale->items()->whereKey($saleItemId)->first();
        throw_unless($item, DomainException::class, 'Cart line not found');

        if ($data->quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update([
            'quantity' => $data->quantity,
            'total' => ((int) $item->price * $data->quantity) - ((int) ($item->discount ?? 0)) + ((int) ($item->tax_amount ?? 0)),
        ]);
    }
}
