<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class ReceivePurchase
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, int $userId): Purchase
    {
        if ($purchase->status->isCompleted()) {
            return $purchase;
        }

        throw_if($purchase->status->isCancelled(), InvalidArgumentException::class, 'Cannot receive a cancelled purchase.');

        return DB::transaction(function () use ($purchase, $userId): Purchase {
            $purchase->update([
                'status' => PurchaseStatusEnum::RECEIVED,
                'updated_by' => $userId,
            ]);

            foreach ($purchase->items as $item) {
                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'store_id' => $purchase->store_id,
                    'quantity' => $item->quantity,
                    'source_type' => Purchase::class,
                    'source_id' => $purchase->id,
                    'batch_number' => $item->batch_number,
                    'notes' => 'Purchase received: '.$purchase->reference,
                    'created_by' => $userId,
                ]);
            }

            return $purchase;
        });
    }
}
