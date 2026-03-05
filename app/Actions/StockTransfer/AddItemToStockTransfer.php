<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\StockTransferItemData;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class AddItemToStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, StockTransferItemData $itemData): StockTransferItem
    {
        return DB::transaction(static fn (): StockTransferItem => StockTransferItem::query()->forceCreate([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $itemData->product_id,
            'batch_id' => $itemData->batch_id,
            'quantity' => $itemData->quantity,
        ])->refresh());
    }
}
