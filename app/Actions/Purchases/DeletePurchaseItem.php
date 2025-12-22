<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Models\PurchaseItem;

final readonly class DeletePurchaseItem
{
    public function handle(PurchaseItem $purchaseItem): bool
    {
        return (bool) $purchaseItem->delete();
    }
}
