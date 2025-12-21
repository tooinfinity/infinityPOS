<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Models\SaleItem;

final readonly class DeleteSaleItem
{
    public function handle(SaleItem $saleItem): bool
    {
        return (bool) $saleItem->delete();
    }
}
