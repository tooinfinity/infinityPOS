<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\StockMovement\CreateStockMovement;
use App\Data\PurchaseReturn\RevertPurchaseReturnData;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RevertPurchaseReturn
{
    public function __construct(
        private CreateStockMovement $createStockMovement,
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
                ->with(['items.batch' => fn ($query) => $query->lockForUpdate()])
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
        if ($purchaseReturn->status !== ReturnStatusEnum::Completed) {
            throw new StateTransitionException(
                $purchaseReturn->status->value,
                'Pending'
            );
        }

        $hasActiveRefunds = $purchaseReturn->payments()
            ->active()
            ->refunds()
            ->exists();

        throw_if($hasActiveRefunds, RefundNotAllowedException::class, 'purchase return', 'Cannot cancel a purchase return that has existing refunds. Please void the refunds first.');
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
            $batch->forceFill(['quantity' => $previousQuantity + $item->quantity])->save();

            $this->createStockMovement->recordIn(
                $batch,
                $item->quantity,
                $previousQuantity,
                $purchaseReturn,
                $purchaseReturn->user_id,
                'Purchase return reverted - stock restored',
            );
        }
    }
}
