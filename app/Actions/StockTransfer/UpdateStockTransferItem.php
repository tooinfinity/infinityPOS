<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\UpdateStockTransferItemData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateStockTransferItem
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransferItem $item, UpdateStockTransferItemData $data): void
    {
        DB::transaction(function () use ($item, $data): void {
            throw_if($item->stockTransfer?->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Items can only be updated when transfer is pending.');

            $updateData = [];

            if (! $data->batch_id instanceof Optional) {
                $updateData['batch_id'] = $data->batch_id;
            }
            if (! $data->quantity instanceof Optional) {
                $updateData['quantity'] = $data->quantity;
            }

            $item->forceFill($updateData)->save();
        });
    }
}
