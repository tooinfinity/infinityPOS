<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\StockTransferData;
use App\Data\StockTransfer\StockTransferItemData;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, StockTransferData $data): StockTransfer
    {
        /** @var StockTransfer $result */
        $result = DB::transaction(static function () use ($transfer, $data): StockTransfer {
            if ($transfer->status !== StockTransferStatusEnum::Pending) {
                throw new InvalidOperationException(
                    'update',
                    'StockTransfer',
                    "Only pending transfers can be edited. Current status: {$transfer->status->label()}."
                );
            }

            $updateData = [
                'from_warehouse_id' => $data->from_warehouse_id ?? $transfer->from_warehouse_id,
                'to_warehouse_id' => $data->to_warehouse_id ?? $transfer->to_warehouse_id,
                'transfer_date' => $data->transfer_date ?? $transfer->transfer_date,
                'note' => $data->note ?? $transfer->note,
            ];

            $transfer->update($updateData);

            $transfer->items()->delete();

            $data->items->toCollection()
                ->each(function (StockTransferItemData $itemData) use ($transfer): void {
                    $transfer->items()->forceCreate([
                        'product_id' => $itemData->product_id,
                        'batch_id' => $itemData->batch_id,
                        'quantity' => $itemData->quantity,
                    ]);
                });

            return $transfer->load(['items.product', 'items.batch']);
        });

        return $result;
    }
}
