<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Data\Purchase\PurchaseItemData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Data\Sale\SaleItemData;
use App\Data\SaleReturn\SaleReturnItemData;
use Spatie\LaravelData\DataCollection;

final readonly class CalculateTotalFromItems
{
    /**
     * @param  DataCollection<int, SaleItemData|SaleReturnItemData|PurchaseItemData|PurchaseReturnItemData>  $items
     */
    public function handle(DataCollection $items, string $priceField = 'unit_price'): int
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item->quantity * $item->{$priceField};
        }

        return $total;
    }
}
