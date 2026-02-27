<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\Shared\ValidateStatusIsPending;
use App\Data\StockTransfer\StockTransferItemData;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class AddItemToStockTransfer
{
    public function __construct(private ValidateStatusIsPending $validateStatus) {}

    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, StockTransferItemData $itemData): StockTransferItem
    {
        return DB::transaction(static function () use ($transfer, $itemData): StockTransferItem {
            throw_if($transfer->status !== \App\Enums\StockTransferStatusEnum::Pending, RuntimeException::class, 'Items can only be added to pending transfers.');

            return StockTransferItem::query()->forceCreate([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $itemData->product_id,
                'batch_id' => $itemData->batch_id,
                'quantity' => $itemData->quantity,
            ])->refresh();
        });
    }
}
