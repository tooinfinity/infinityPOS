<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Data\Sale\SaleItemData;
use Spatie\LaravelData\DataCollection;

final readonly class CalculateSaleTotal
{
    /**
     * @param  DataCollection<int, SaleItemData>  $items
     */
    public function handle(DataCollection $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $quantity = $item->quantity;
            $unitPrice = $item->unit_price;
            $total += $quantity * $unitPrice;
        }

        return $total;
    }
}
