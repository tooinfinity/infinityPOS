<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelPurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, int $userId): Purchase
    {
        if ($purchase->status->isCancelled()) {
            return $purchase;
        }

        return DB::transaction(static function () use ($purchase, $userId): Purchase {
            if ($purchase->status->isCompleted()) {
                foreach ($purchase->items as $item) {
                    StockMovement::query()->create([
                        'product_id' => $item->product_id,
                        'store_id' => $purchase->store_id,
                        'quantity' => -$item->quantity,
                        'source_type' => Purchase::class,
                        'source_id' => $purchase->id,
                        'batch_number' => $item->batch_number,
                        'notes' => 'Purchase cancelled: '.$purchase->reference,
                        'created_by' => $userId,
                    ]);
                }
            }

            $purchase->update([
                'status' => PurchaseStatusEnum::CANCELLED,
                'updated_by' => $userId,
            ]);

            return $purchase;
        });
    }
}
