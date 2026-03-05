<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\UpdateStockTransferItemData;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final class UpdateStockTransferItem
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransferItem $item, UpdateStockTransferItemData $data): StockTransferItem
    {
        return DB::transaction(static function () use ($item, $data): StockTransferItem {
            $updateData = [];

            if (! $data->batch_id instanceof Optional) {
                $updateData['batch_id'] = $data->batch_id;
            }
            if (! $data->quantity instanceof Optional) {
                $updateData['quantity'] = $data->quantity;
            }

            $item->update($updateData);

            return $item->refresh();
        });
    }
}
