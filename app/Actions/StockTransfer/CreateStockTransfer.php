<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\GenerateReferenceNo;
use App\Data\StockTransfer\StockTransferData;
use App\Data\StockTransfer\StockTransferItemData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateStockTransfer
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(StockTransferData $data): StockTransfer
    {
        /** @var StockTransfer $transfer */
        $transfer = DB::transaction(function () use ($data): StockTransfer {
            $transfer = StockTransfer::query()->forceCreate([
                'from_warehouse_id' => $data->from_warehouse_id,
                'to_warehouse_id' => $data->to_warehouse_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('TRF', StockTransfer::class),
                'status' => StockTransferStatusEnum::Pending,
                'transfer_date' => $data->transfer_date,
                'note' => $data->note,
            ]);

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

        return $transfer;
    }
}
