<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\Stock\TransferStock;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\InvalidBatchException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteStockTransfer
{
    public function __construct(
        private TransferStock $transferStock,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer): StockTransfer
    {
        /** @var StockTransfer $transfer */
        $transfer = DB::transaction(function () use ($transfer): StockTransfer {
            if (! $transfer->status->canTransitionTo(StockTransferStatusEnum::Completed)) {
                throw new StateTransitionException(
                    $transfer->status->value,
                    StockTransferStatusEnum::Completed->value,
                );
            }

            $transfer->load('items.batch');

            foreach ($transfer->items as $item) {
                if (! $item->batch instanceof Batch) {
                    /** @var int $itemBatchId */
                    $itemBatchId = $item->batch_id ?? null;
                    throw new InvalidBatchException(
                        $itemBatchId,
                        "Batch not found for product #$item->product_id in transfer $transfer->reference_no."
                    );
                }

                if ($item->batch->warehouse_id !== $transfer->from_warehouse_id) {
                    /** @var int $itemBatchId */
                    $itemBatchId = $item->batch_id ?? null;
                    throw new InvalidBatchException(
                        $itemBatchId,
                        "Batch #$item->batch_id does not belong to the source warehouse."
                    );
                }

                $this->transferStock->handle(
                    sourceBatch: $item->batch,
                    destinationWarehouseId: $transfer->to_warehouse_id,
                    quantity: $item->quantity,
                    transfer: $transfer,
                );
            }

            $transfer->forceFill([
                'status' => StockTransferStatusEnum::Completed,
            ])->save();

            return $transfer->refresh();
        });

        return $transfer;
    }
}
