<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

final readonly class CreateSaleItems
{
    /**
     * @param  Collection<int, \App\Data\Sale\SaleItemData>|DataCollection<int, \App\Data\Sale\SaleItemData>  $items
     */
    public function handle(int $saleId, Collection|DataCollection $items): void
    {
        foreach ($items as $item) {
            SaleItem::query()->forceCreate([
                'sale_id' => $saleId,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'unit_cost' => $item->unit_cost,
                'subtotal' => $item->quantity * $item->unit_price,
            ]);
        }
    }
}
