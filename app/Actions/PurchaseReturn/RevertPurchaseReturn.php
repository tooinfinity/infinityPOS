<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Stock\AdjustBatchQuantity;
use App\Data\PurchaseReturn\RevertPurchaseReturnData;
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
    public function __construct(
        private AdjustBatchQuantity $adjustBatchQuantity,
    ) {}

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

        throw_if($hasRefunds, RefundNotAllowedException::class, 'purchase return', 'Cannot cancel a purchase return that has existing refunds. Please void the refunds first.');

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

            $this->adjustBatchQuantity->handle(
                $batch,
                $item->quantity,
                StockMovementTypeEnum::In,
                $purchaseReturn,
                'Purchase return reverted - stock restored',
                $purchaseReturn->user_id,
            );
        }
    }
}
