<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Data\Purchases\UpdatePurchaseItemData;
use App\Models\PurchaseItem;

final readonly class UpdatePurchaseItem
{
    public function handle(PurchaseItem $purchaseItem, UpdatePurchaseItemData $data): PurchaseItem
    {
        $updateData = array_filter([
            'quantity' => $data->quantity,
            'cost' => $data->cost,
            'discount' => $data->discount,
            'tax_amount' => $data->tax_amount,
            'total' => $data->total,
            'batch_number' => $data->batch_number,
            'expiry_date' => $data->expiry_date,
        ], fn (int|string|null $value): bool => $value !== null);

        $purchaseItem->update($updateData);

        return $purchaseItem;
    }
}
