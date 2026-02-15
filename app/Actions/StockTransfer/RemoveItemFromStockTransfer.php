<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class RemoveItemFromStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, StockTransferItem $item): bool
    {
        return DB::transaction(function () use ($transfer, $item): bool {
            throw_if($transfer->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Items can only be removed from pending transfers.');

            throw_if($item->stock_transfer_id !== $transfer->id, RuntimeException::class, 'Item does not belong to this transfer.');

            return (bool) $item->delete();
        });
    }
}
