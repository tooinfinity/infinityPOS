<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Data\PurchaseReturn\UpdatePurchaseReturnItemData;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdatePurchaseReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturnItem $item, UpdatePurchaseReturnItemData $data): PurchaseReturnItem
    {
        return DB::transaction(function () use ($item, $data): PurchaseReturnItem {
            $purchaseReturn = $item->purchaseReturn;

            $this->validatePurchaseReturnIsPending($purchaseReturn);

            if ($data->quantity !== null) {
                $item->forceFill([
                    'quantity' => $data->quantity,
                    'subtotal' => $data->quantity * $item->unit_cost,
                ]);
            }

            if ($data->unit_cost !== null) {
                $item->forceFill([
                    'unit_cost' => $data->unit_cost,
                    'subtotal' => $item->quantity * $data->unit_cost,
                ]);
            }

            $item->save();

            $this->recalculateTotalAmount($purchaseReturn);

            return $item->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validatePurchaseReturnIsPending(PurchaseReturn $purchaseReturn): void
    {
        throw_if($purchaseReturn->status->value !== 'pending', RuntimeException::class, 'Cannot update items in a non-pending purchase return.');
    }

    private function recalculateTotalAmount(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->refresh();

        $totalAmount = $purchaseReturn->items()->lockForUpdate()->sum('subtotal');

        $purchaseReturn->forceFill([
            'total_amount' => $totalAmount,
        ])->save();
    }
}
