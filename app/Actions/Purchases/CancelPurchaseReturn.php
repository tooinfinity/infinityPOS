<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Enums\PurchaseReturnStatusEnum;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelPurchaseReturn
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, int $userId): PurchaseReturn
    {
        if ($purchaseReturn->status->isCancelled()) {
            return $purchaseReturn;
        }

        return DB::transaction(function () use ($purchaseReturn, $userId): PurchaseReturn {
            if ($purchaseReturn->status->isCompleted()) {
                foreach ($purchaseReturn->items as $item) {
                    StockMovement::query()->create([
                        'product_id' => $item->product_id,
                        'store_id' => $purchaseReturn->store_id,
                        'quantity' => $item->quantity,
                        'source_type' => PurchaseReturn::class,
                        'source_id' => $purchaseReturn->id,
                        'batch_number' => $item->batch_number,
                        'notes' => 'Purchase return cancelled: '.$purchaseReturn->reference,
                        'created_by' => $userId,
                    ]);
                }
            }

            $purchaseReturn->update([
                'status' => PurchaseReturnStatusEnum::CANCELLED,
                'updated_by' => $userId,
            ]);

            return $purchaseReturn;
        });
    }
}
