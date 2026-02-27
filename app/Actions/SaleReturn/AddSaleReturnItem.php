<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateReturnAgainstOriginal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class AddSaleReturnItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private ValidateReturnAgainstOriginal $validateReturn,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, SaleReturnItemData $data): SaleReturnItem
    {
        return DB::transaction(function () use ($saleReturn, $data): SaleReturnItem {
            /** @var SaleReturn $saleReturn */
            $saleReturn = SaleReturn::query()
                ->lockForUpdate()
                ->with('sale.items')
                ->findOrFail($saleReturn->id);

            $this->validateStatus->handle($saleReturn);
            $this->validateReturn->validateNewReturn($saleReturn, $data->product_id, $data->batch_id, $data->quantity);

            $item = SaleReturnItem::query()->forceCreate([
                'sale_return_id' => $saleReturn->id,
                'product_id' => $data->product_id,
                'batch_id' => $data->batch_id,
                'quantity' => $data->quantity,
                'unit_price' => $data->unit_price,
                'subtotal' => $data->quantity * $data->unit_price,
            ]);

            $this->recalculateTotal->handle($saleReturn);

            return $item;
        });
    }
}
