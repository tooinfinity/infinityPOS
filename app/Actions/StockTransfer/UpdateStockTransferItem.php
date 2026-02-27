<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\Shared\ValidateStatusIsPending;
use App\Data\StockTransfer\UpdateStockTransferItemData;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateStockTransferItem
{
    public function __construct(private ValidateStatusIsPending $validateStatus) {}

    /**
     * @throws Throwable
     */
    public function handle(StockTransferItem $item, UpdateStockTransferItemData $data): StockTransferItem
    {
        return DB::transaction(function () use ($item, $data): StockTransferItem {
            $this->validateStatus->forItem($item, 'Items can only be updated when transfer is pending.');

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
