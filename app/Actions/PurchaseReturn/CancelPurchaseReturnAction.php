<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\PurchaseReturn\CancelPurchaseReturnData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\ReturnStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CancelPurchaseReturnAction
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, CancelPurchaseReturnData $data): PurchaseReturn
    {
        return DB::transaction(function () use ($purchaseReturn, $data): PurchaseReturn {
            $this->validatePurchaseReturnCanBeCancelled($purchaseReturn);

            if ($purchaseReturn->status === ReturnStatusEnum::Completed) {
                $this->addStockToBatches($purchaseReturn);
            }

            $purchaseReturn->forceFill([
                'status' => ReturnStatusEnum::Pending,
                'note' => $data->note ?? $purchaseReturn->note,
            ])->save();

            return $purchaseReturn->refresh();
        });
    }

    private function validatePurchaseReturnCanBeCancelled(PurchaseReturn $purchaseReturn): void
    {
        $hasRefunds = $purchaseReturn->payments()
            ->where('amount', '<', 0)
            ->exists();

        throw_if($hasRefunds, RuntimeException::class, 'Cannot cancel a purchase return that has existing refunds. Please void the refunds first.');

        if ($purchaseReturn->status !== ReturnStatusEnum::Completed) {
            throw new RuntimeException(
                "Purchase return cannot be cancelled. Current status: {$purchaseReturn->status->value}"
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function addStockToBatches(PurchaseReturn $purchaseReturn): void
    {
        foreach ($purchaseReturn->items as $item) {
            $batch = $item->batch;

            if ($batch === null) {
                continue;
            }

            $previousQuantity = $batch->quantity;

            $batch->forceFill(['quantity' => $batch->quantity + $item->quantity])->save();

            $this->recordStockMovement->handle(new RecordStockMovementData(
                warehouse_id: $purchaseReturn->warehouse_id,
                product_id: $item->product_id,
                type: StockMovementTypeEnum::In,
                quantity: $item->quantity,
                previous_quantity: $previousQuantity,
                current_quantity: $previousQuantity + $item->quantity,
                reference_type: PurchaseReturn::class,
                reference_id: $purchaseReturn->id,
                batch_id: $batch->id,
                user_id: $purchaseReturn->user_id,
                note: 'Purchase return cancelled - stock added back',
                created_at: null,
            ));
        }
    }
}
