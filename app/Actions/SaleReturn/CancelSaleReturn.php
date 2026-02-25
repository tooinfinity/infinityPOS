<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\SaleReturn\CancelSaleReturnData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\ReturnStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CancelSaleReturn
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, CancelSaleReturnData $data): SaleReturn
    {
        return DB::transaction(function () use ($saleReturn, $data): SaleReturn {
            $this->validateSaleReturnCanBeCancelled($saleReturn);

            $saleReturn->loadMissing('items.batch');

            if ($saleReturn->status === ReturnStatusEnum::Completed) {
                $this->removeStockFromBatches($saleReturn);
            }

            $saleReturn->forceFill([
                'status' => ReturnStatusEnum::Pending,
                'note' => $data->note ?? $saleReturn->note,
            ])->save();

            return $saleReturn->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateSaleReturnCanBeCancelled(SaleReturn $saleReturn): void
    {
        $hasRefunds = $saleReturn->payments()
            ->where('amount', '<', 0)
            ->exists();

        throw_if($hasRefunds, RuntimeException::class, 'Cannot cancel a sale return that has existing refunds. Please void the refunds first.');

        if ($saleReturn->status !== ReturnStatusEnum::Completed) {
            throw new RuntimeException(
                "Can only cancel completed sale returns. Use DeleteSaleReturn to delete pending returns. Current status: {$saleReturn->status->value}"
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function removeStockFromBatches(SaleReturn $saleReturn): void
    {
        foreach ($saleReturn->items as $item) {
            $batch = $item->batch()->lockForUpdate()->first();

            if ($batch === null) {
                continue;
            }

            $previousQuantity = $batch->quantity;

            $newQuantity = $batch->quantity - $item->quantity;

            if ($newQuantity < 0) {
                throw new RuntimeException(
                    "Cannot cancel sale return. Insufficient stock in batch. Available: $batch->quantity, Required: $item->quantity"
                );
            }

            $batch->forceFill(['quantity' => $newQuantity])->save();

            $this->recordStockMovement->handle(new RecordStockMovementData(
                warehouse_id: $saleReturn->warehouse_id,
                product_id: $item->product_id,
                type: StockMovementTypeEnum::Out,
                quantity: $item->quantity,
                previous_quantity: $previousQuantity,
                current_quantity: $newQuantity,
                reference_type: SaleReturn::class,
                reference_id: $saleReturn->id,
                batch_id: $batch->id,
                user_id: $saleReturn->user_id,
                note: 'Sale return cancelled - stock removed',
            ));
        }
    }
}
