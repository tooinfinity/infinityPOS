<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Stock\ValidateStockForPendingSale;
use App\Data\Sale\SaleItemData;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class AddSaleItem
{
    public function __construct(
        private RecalculateParentTotal $recalculateTotal,
        private ValidateStockForPendingSale $validateStockForPendingSale,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, SaleItemData $data): SaleItem
    {
        return DB::transaction(function () use ($sale, $data): SaleItem {
            $this->validateStockForPendingSale->handle($sale, $data->batch_id, $data->quantity, null, $data->product_id);

            $item = SaleItem::query()->forceCreate([
                'sale_id' => $sale->id,
                'product_id' => $data->product_id,
                'batch_id' => $data->batch_id,
                'quantity' => $data->quantity,
                'unit_price' => $data->unit_price,
                'unit_cost' => $data->unit_cost,
                'subtotal' => $data->quantity * $data->unit_price,
            ]);

            $this->recalculateTotal->handle($sale);

            return $item;
        });
    }
}
