<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class AddPurchaseReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, PurchaseReturnItemData $data): PurchaseReturnItem
    {
        return DB::transaction(function () use ($purchaseReturn, $data): PurchaseReturnItem {
            $this->validatePurchaseReturnIsPending($purchaseReturn);

            $item = PurchaseReturnItem::query()->forceCreate([
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $data->product_id,
                'batch_id' => $data->batch_id,
                'quantity' => $data->quantity,
                'unit_cost' => $data->unit_cost,
                'subtotal' => $data->quantity * $data->unit_cost,
            ]);

            $this->recalculateTotalAmount($purchaseReturn);

            return $item;
        });
    }

    /**
     * @throws Throwable
     */
    private function validatePurchaseReturnIsPending(PurchaseReturn $purchaseReturn): void
    {
        throw_if($purchaseReturn->status->value !== 'pending', RuntimeException::class, 'Cannot add items to a non-pending purchase return.');
    }

    private function recalculateTotalAmount(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->refresh();

        $totalAmount = $purchaseReturn->items()->sum('subtotal');

        $purchaseReturn->forceFill([
            'total_amount' => $totalAmount,
        ])->save();
    }
}
