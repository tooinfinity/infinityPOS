<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\StockMovement\CreateStockMovement;
use App\Data\SaleReturn\RevertSaleReturnData;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RevertSaleReturn
{
    public function __construct(
        private CreateStockMovement $createStockMovement,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, RevertSaleReturnData $data): SaleReturn
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

        throw_if($hasRefunds, RefundNotAllowedException::class, 'sale return', 'Cannot cancel a sale return that has existing refunds. Please void the refunds first.');

        if ($saleReturn->status !== ReturnStatusEnum::Completed) {
            throw new StateTransitionException(
                $saleReturn->status->value,
                'Pending'
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
            $newQuantity = $previousQuantity - $item->quantity;

            if ($newQuantity < 0) {
                throw new InsufficientStockException(
                    required: $item->quantity,
                    available: $previousQuantity,
                    batchId: $batch->id,
                );
            }

            $batch->forceFill(['quantity' => $newQuantity])->save();

            $this->createStockMovement->recordOut(
                $batch,
                $item->quantity,
                $previousQuantity,
                $saleReturn,
                $saleReturn->user_id,
                'Sale return cancelled - stock removed',
            );
        }
    }
}
