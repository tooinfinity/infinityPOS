<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Data\Purchases\UpdatePurchaseData;
use App\Models\Purchase;

final readonly class UpdatePurchase
{
    public function handle(Purchase $purchase, UpdatePurchaseData $data): Purchase
    {
        $updateData = array_filter([
            'reference' => $data->reference,
            'supplier_id' => $data->supplier_id,
            'store_id' => $data->store_id,
            'subtotal' => $data->subtotal,
            'discount' => $data->discount,
            'tax' => $data->tax,
            'total' => $data->total,
            'notes' => $data->notes,
            'updated_by' => $data->updated_by,
        ], fn (string|int|null $value): bool => $value !== null);

        $purchase->update($updateData);

        return $purchase;
    }
}
