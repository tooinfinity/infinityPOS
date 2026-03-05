<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateReturnAgainstOriginal;
use App\Data\SaleReturn\UpdateSaleReturnItemData;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateSaleReturnItem
{
    public function __construct(
        private ValidateReturnAgainstOriginal $validateReturn,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturnItem $item, UpdateSaleReturnItemData $data): SaleReturnItem
    {
        return DB::transaction(function () use ($item, $data): SaleReturnItem {
            /** @var SaleReturnItem $item */
            $item = SaleReturnItem::query()
                ->lockForUpdate()
                ->with('saleReturn.sale.items')
                ->findOrFail($item->id);

            $saleReturn = $item->saleReturn;

            $quantity = $data->quantity ?? $item->quantity;
            $unitPrice = $data->unit_price ?? $item->unit_price;

            if ($data->quantity !== null) {
                $this->validateReturn->handle($item, $item->product_id, $item->batch_id, $quantity);
            }

            $item->forceFill([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
            ])->save();

            $this->recalculateTotal->handle($saleReturn);

            return $item->refresh();
        });
    }
}
