<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Data\Purchases\CreatePurchaseItemData;
use App\Models\Purchase;
use App\Models\PurchaseItem;

final readonly class CreatePurchaseItem
{
    public function handle(Purchase $purchase, CreatePurchaseItemData $data): PurchaseItem
    {
        return $purchase->items()->create([
            'product_id' => $data->product_id,
            'quantity' => $data->quantity,
            'cost' => $data->cost,
            'discount' => $data->discount,
            'tax_amount' => $data->tax_amount,
            'total' => $data->total,
            'batch_number' => $data->batch_number,
            'expiry_date' => $data->expiry_date,
        ]);
    }
}
