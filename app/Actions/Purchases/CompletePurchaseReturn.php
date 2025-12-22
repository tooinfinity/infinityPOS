<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Enums\PurchaseReturnStatusEnum;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CompletePurchaseReturn
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, int $userId): PurchaseReturn
    {
        if ($purchaseReturn->status->isCompleted()) {
            return $purchaseReturn;
        }

        throw_if($purchaseReturn->status->isCancelled(), InvalidArgumentException::class, 'Cannot complete a cancelled purchase return.');

        return DB::transaction(function () use ($purchaseReturn, $userId): PurchaseReturn {
            $purchaseReturn->update([
                'status' => PurchaseReturnStatusEnum::COMPLETED,
                'updated_by' => $userId,
            ]);

            foreach ($purchaseReturn->items as $item) {
                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'store_id' => $purchaseReturn->store_id,
                    'quantity' => -$item->quantity,
                    'source_type' => PurchaseReturn::class,
                    'source_id' => $purchaseReturn->id,
                    'batch_number' => $item->batch_number,
                    'notes' => 'Purchase return completed: '.$purchaseReturn->reference,
                    'created_by' => $userId,
                ]);
            }

            return $purchaseReturn;
        });
    }
}
