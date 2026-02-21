<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use RuntimeException;
use Throwable;

final readonly class RemoveSaleReturnItemAction
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturnItem $item): bool
    {
        $saleReturn = $item->saleReturn;

        $this->validateSaleReturnIsPending($saleReturn);

        $deleted = $item->delete();

        if ($deleted) {
            $this->recalculateTotalAmount($saleReturn);
        }

        return (bool) $deleted;
    }

    /**
     * @throws Throwable
     */
    private function validateSaleReturnIsPending(SaleReturn $saleReturn): void
    {
        throw_if($saleReturn->status->value !== 'pending', RuntimeException::class, 'Cannot remove items from a non-pending sale return.');
    }

    private function recalculateTotalAmount(SaleReturn $saleReturn): void
    {
        $saleReturn->refresh();

        $totalAmount = $saleReturn->items()->sum('subtotal');

        $saleReturn->forceFill([
            'total_amount' => $totalAmount,
        ])->save();
    }
}
