<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\StockTransferItemData;
use App\Exceptions\InvalidOperationException;
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
        return DB::transaction(static function () use ($transfer, $itemData): StockTransferItem {
            if ($transfer->status !== \App\Enums\StockTransferStatusEnum::Pending) {
                throw new InvalidOperationException(
                    'add item to',
                    'StockTransfer',
                    'Items can only be added to pending transfers.'
                );
            }

            return StockTransferItem::query()->forceCreate([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $itemData->product_id,
                'batch_id' => $itemData->batch_id,
                'quantity' => $itemData->quantity,
            ])->refresh();
        });
    }
}
