<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\PurchaseReturn\CompletePurchaseReturnData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\ReturnStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompletePurchaseReturn
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, CompletePurchaseReturnData $data): PurchaseReturn
    {
        return DB::transaction(function () use ($purchaseReturn, $data): PurchaseReturn {
            /** @var PurchaseReturn $purchaseReturn */
            $purchaseReturn = PurchaseReturn::query()
                ->lockForUpdate()
                ->with('items')
                ->findOrFail($purchaseReturn->id);

            $this->validatePurchaseReturnCanBeCompleted($purchaseReturn);

            $this->removeStockFromBatches($purchaseReturn);

            $purchaseReturn->forceFill([
                'status' => ReturnStatusEnum::Completed,
                'note' => $data->note ?? $purchaseReturn->note,
            ])->save();

            return $purchaseReturn->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validatePurchaseReturnCanBeCompleted(PurchaseReturn $purchaseReturn): void
    {
        if ($purchaseReturn->status !== ReturnStatusEnum::Pending) {
            throw new StateTransitionException(
                $purchaseReturn->status->value,
                'Completed'
            );
        }

        if ($purchaseReturn->items->isEmpty()) {
            throw new InvalidOperationException(
                'complete',
                'PurchaseReturn',
                'Purchase return cannot be completed without items'
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function removeStockFromBatches(PurchaseReturn $purchaseReturn): void
    {
        foreach ($purchaseReturn->items as $item) {
            $batch = $item->batch()->lockForUpdate()->first();

            if ($batch === null) {
                continue;
            }

            $previousQuantity = $batch->quantity;

            $newQuantity = $batch->quantity - $item->quantity;

            if ($newQuantity < 0) {
                throw new InsufficientStockException(
                    required: $item->quantity,
                    available: $batch->quantity,
                    batchId: $batch->id
                );
            }

            $batch->forceFill(['quantity' => $newQuantity])->save();

            $this->recordStockMovement->handle(new RecordStockMovementData(
                warehouse_id: $purchaseReturn->warehouse_id,
                product_id: $item->product_id,
                type: StockMovementTypeEnum::Out,
                quantity: $item->quantity,
                previous_quantity: $previousQuantity,
                current_quantity: $newQuantity,
                reference_type: PurchaseReturn::class,
                reference_id: $purchaseReturn->id,
                batch_id: $batch->id,
                user_id: $purchaseReturn->user_id,
                note: 'Purchase return completed - stock removed',
            ));
        }
    }
}
