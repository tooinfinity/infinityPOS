<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Shared\ValidateStatusIsPending;
use App\Actions\Stock\AdjustBatchQuantity;
use App\Data\PurchaseReturn\CompletePurchaseReturnData;
use App\Enums\ReturnStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompletePurchaseReturn
{
    public function __construct(
        private AdjustBatchQuantity $adjustBatchQuantity,
        private ValidateStatusIsPending $validateStatus,
    ) {}

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
        $this->validateStatus->validateTransition(
            $purchaseReturn->status,
            ReturnStatusEnum::Completed,
            'PurchaseReturn'
        );

        throw_if($purchaseReturn->items->isEmpty(), InvalidOperationException::class, 'complete', 'PurchaseReturn', 'Purchase return cannot be completed without items');
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

            $this->adjustBatchQuantity->handle(
                $batch,
                -$item->quantity,
                StockMovementTypeEnum::Out,
                $purchaseReturn,
                'Purchase return completed - stock removed',
                $purchaseReturn->user_id,
            );
        }
    }
}
