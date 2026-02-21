<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Data\SaleReturn\SaleReturnItemData;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use RuntimeException;

final readonly class AddSaleReturnItemAction
{
    public function handle(SaleReturn $saleReturn, SaleReturnItemData $data): SaleReturnItem
    {
        $this->validateSaleReturnIsPending($saleReturn);

        $item = SaleReturnItem::query()->forceCreate([
            'sale_return_id' => $saleReturn->id,
            'product_id' => $data->product_id,
            'batch_id' => $data->batch_id,
            'quantity' => $data->quantity,
            'unit_price' => $data->unit_price,
            'subtotal' => $data->quantity * $data->unit_price,
        ]);

        $this->recalculateTotalAmount($saleReturn);

        return $item;
    }

    private function validateSaleReturnIsPending(SaleReturn $saleReturn): void
    {
        throw_if($saleReturn->status->value !== 'pending', RuntimeException::class, 'Cannot add items to a non-pending sale return.');
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
