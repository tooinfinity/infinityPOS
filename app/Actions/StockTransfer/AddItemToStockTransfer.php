<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\StockTransferItemData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class AddItemToStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, StockTransferItemData $itemData): StockTransferItem
    {
        return DB::transaction(static function () use ($transfer, $itemData): StockTransferItem {
            throw_if($transfer->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Items can only be added to pending transfers.');

            return StockTransferItem::query()->create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $itemData->product_id,
                'batch_id' => $itemData->batch_id,
                'quantity' => $itemData->quantity,
            ]);
        });
    }
}
