<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\PurchaseReturn\RevertPurchaseReturnData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\ReturnStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\PurchaseReturn;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RevertPurchaseReturn
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, RevertPurchaseReturnData $data): PurchaseReturn
    {
        return DB::transaction(function () use ($purchaseReturn, $data): PurchaseReturn {
            /** @var PurchaseReturn $purchaseReturn */
            $purchaseReturn = PurchaseReturn::query()
                ->lockForUpdate()
                ->with(['items.batch' => fn (Relation $query) => $query->lockForUpdate()])
                ->findOrFail($purchaseReturn->id);
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

    /**
     * @throws Throwable
     */
    private function validatePurchaseReturnCanBeCancelled(PurchaseReturn $purchaseReturn): void
    {
        $hasRefunds = $purchaseReturn->payments()
            ->where('amount', '<', 0)
            ->exists();

        if ($hasRefunds) {
            throw new RefundNotAllowedException(
                'purchase return',
                'Cannot cancel a purchase return that has existing refunds. Please void the refunds first.'
            );
        }

        if ($purchaseReturn->status !== ReturnStatusEnum::Completed) {
            throw new StateTransitionException(
                $purchaseReturn->status->value,
                'Pending'
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
                note: 'Purchase return reverted - stock restored',
            ));
        }
    }
}
