<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class RemovePurchaseReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturnItem $item): bool
    {
        return DB::transaction(function () use ($item): bool {
            $purchaseReturn = $item->purchaseReturn;

            $this->validatePurchaseReturnIsPending($purchaseReturn);

            $deleted = $item->delete();

            if ($deleted) {
                $this->recalculateTotalAmount($purchaseReturn);
            }

            return (bool) $deleted;
        });
    }

    /**
     * @throws Throwable
     */
    private function validatePurchaseReturnIsPending(PurchaseReturn $purchaseReturn): void
    {
        throw_if($purchaseReturn->status->value !== 'pending', RuntimeException::class, 'Cannot remove items from a non-pending purchase return.');
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
