<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\GenerateReferenceNo;
use App\Data\StockTransfer\CreateStockTransferData;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\WarehouseSameException;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateStockTransfer
{
    public function __construct(private GenerateReferenceNo $generateReferenceNo) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateStockTransferData $data): StockTransfer
    {
        return DB::transaction(function () use ($data): StockTransfer {
            throw_if($data->from_warehouse_id === $data->to_warehouse_id, WarehouseSameException::class);

            $transfer = StockTransfer::query()->forceCreate([
                'from_warehouse_id' => $data->from_warehouse_id,
                'to_warehouse_id' => $data->to_warehouse_id,
                'reference_no' => $this->generateReferenceNo->handle('STK', StockTransfer::class),
                'status' => StockTransferStatusEnum::Pending,
                'note' => $data->note,
                'transfer_date' => $data->transfer_date,
                'user_id' => $data->user_id,
            ]);

            foreach ($data->items as $item) {
                StockTransferItem::query()->forceCreate([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                    'quantity' => $item->quantity,
                ]);
            }

            return $transfer->refresh();
        });
    }
}
