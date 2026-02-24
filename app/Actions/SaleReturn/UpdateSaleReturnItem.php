<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Data\SaleReturn\UpdateSaleReturnItemData;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdateSaleReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturnItem $item, UpdateSaleReturnItemData $data): SaleReturnItem
    {
        return DB::transaction(function () use ($item, $data): SaleReturnItem {
            $saleReturn = $item->saleReturn;

            $this->validateSaleReturnIsPending($saleReturn);

            if ($data->quantity !== null) {
                $item->forceFill([
                    'quantity' => $data->quantity,
                    'subtotal' => $data->quantity * $item->unit_price,
                ]);
            }

            if ($data->unit_price !== null) {
                $item->forceFill([
                    'unit_price' => $data->unit_price,
                    'subtotal' => $item->quantity * $data->unit_price,
                ]);
            }

            $item->save();

            $this->recalculateTotalAmount($saleReturn);

            return $item->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateSaleReturnIsPending(SaleReturn $saleReturn): void
    {
        throw_if($saleReturn->status->value !== 'pending', RuntimeException::class, 'Cannot update items in a non-pending sale return.');
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
