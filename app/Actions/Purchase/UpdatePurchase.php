<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Data\Purchase\PurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdatePurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, PurchaseData $data): Purchase
    {
        /** @var Purchase $result */
        $result = DB::transaction(static function () use ($purchase, $data): Purchase {
            if ($purchase->status !== PurchaseStatusEnum::Pending) {
                throw new InvalidOperationException(
                    'update',
                    'Purchase',
                    "Only pending purchases can be edited. Current status: {$purchase->status->label()}."
                );
            }

            $updatedData = [
                'supplier_id' => $data->supplier_id ?? $purchase->supplier_id,
                'warehouse_id' => $data->warehouse_id ?? $purchase->warehouse_id,
                'purchase_date' => $data->purchase_date ?? $purchase->purchase_date,
                'total_amount' => $data->total_amount ?? $purchase->total_amount,
                'note' => $data->note ?? $purchase->note,
            ];
            $purchase->update($updatedData);

            $purchase->items()->delete();

            $data->items->toCollection()
                ->each(function (PurchaseItemData $itemData) use ($purchase): void {
                    $purchase->items()->forceCreate([
                        'product_id' => $itemData->product_id,
                        'quantity' => $itemData->quantity,
                        'received_quantity' => 0,
                        'unit_cost' => $itemData->unit_cost,
                        'subtotal' => $itemData->unit_cost * $itemData->quantity,
                        'expires_at' => $itemData->expires_at,
                    ]);
                });

            return $purchase->load(['items.product', 'supplier', 'warehouse']);
        });

        return $result;
    }
}
