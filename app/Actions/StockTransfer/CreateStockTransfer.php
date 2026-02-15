<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Data\StockTransfer\CreateStockTransferData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final readonly class CreateStockTransfer
{
    /**
     * @throws Throwable
     */
    public function handle(CreateStockTransferData $data): StockTransfer
    {
        return DB::transaction(function () use ($data): StockTransfer {
            throw_if($data->from_warehouse_id === $data->to_warehouse_id, RuntimeException::class, 'Source and destination warehouse cannot be the same.');

            $transfer = new StockTransfer();
            $transfer->forceFill([
                'from_warehouse_id' => $data->from_warehouse_id,
                'to_warehouse_id' => $data->to_warehouse_id,
                'reference_no' => $this->generateReferenceNo(),
                'status' => StockTransferStatusEnum::Pending,
                'note' => $data->note,
                'transfer_date' => $data->transfer_date,
                'user_id' => $data->user_id,
            ])->save();

            foreach ($data->items as $item) {
                $transferItem = new StockTransferItem();
                $transferItem->forceFill([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                    'quantity' => $item->quantity,
                ])->save();
            }

            return $transfer;
        });
    }

    private function generateReferenceNo(): string
    {
        return 'STF-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
