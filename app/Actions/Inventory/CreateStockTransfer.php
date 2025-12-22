<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Data\Inventory\CreateStockTransferData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(CreateStockTransferData $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $transfer = StockTransfer::query()->create([
                'reference' => $data->reference,
                'from_store_id' => $data->from_store_id,
                'to_store_id' => $data->to_store_id,
                'status' => StockTransferStatusEnum::PENDING,
                'notes' => $data->notes,
                'created_by' => $data->created_by,
            ]);

            foreach ($data->items as $itemData) {
                $transfer->items()->create([
                    'product_id' => $itemData->product_id,
                    'quantity' => $itemData->quantity,
                    'batch_number' => $itemData->batch_number,
                ]);
            }

            return $transfer;
        });
    }
}
