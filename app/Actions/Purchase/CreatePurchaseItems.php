<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Data\Purchase\PurchaseItemData;
use App\Models\PurchaseItem;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

final readonly class CreatePurchaseItems
{
    /**
     * @param  Collection<int, PurchaseItemData>|DataCollection<int, PurchaseItemData>  $items
     */
    public function handle(int $purchaseId, Collection|DataCollection $items): void
    {
        foreach ($items as $item) {
            PurchaseItem::query()->forceCreate([
                'purchase_id' => $purchaseId,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'received_quantity' => 0,
                'unit_cost' => $item->unit_cost,
                'subtotal' => $item->quantity * $item->unit_cost,
            ]);
        }
    }
}
