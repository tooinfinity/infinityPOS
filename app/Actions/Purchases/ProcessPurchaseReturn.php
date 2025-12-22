<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Data\Purchases\ProcessPurchaseReturnData;
use App\Enums\PurchaseReturnStatusEnum;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessPurchaseReturn
{
    /**
     * @throws Throwable
     */
    public function handle(ProcessPurchaseReturnData $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            $purchaseReturn = PurchaseReturn::query()->create([
                'reference' => $data->reference,
                'purchase_id' => $data->purchase_id,
                'supplier_id' => $data->supplier_id,
                'store_id' => $data->store_id,
                'subtotal' => $data->subtotal,
                'discount' => $data->discount,
                'tax' => $data->tax,
                'total' => $data->total,
                'refunded' => 0,
                'status' => PurchaseReturnStatusEnum::PENDING,
                'reason' => $data->reason,
                'notes' => $data->notes,
                'created_by' => $data->created_by,
            ]);

            foreach ($data->items as $itemData) {
                $purchaseReturn->items()->create([
                    'product_id' => $itemData->product_id,
                    'purchase_item_id' => $itemData->purchase_item_id,
                    'quantity' => $itemData->quantity,
                    'cost' => $itemData->cost,
                    'total' => $itemData->total,
                    'batch_number' => $itemData->batch_number,
                ]);
            }

            return $purchaseReturn;
        });
    }
}
