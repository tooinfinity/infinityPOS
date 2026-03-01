<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\SaleReturn\CompleteSaleReturnData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\ReturnStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteSaleReturn
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, CompleteSaleReturnData $data): SaleReturn
    {
        return DB::transaction(function () use ($saleReturn, $data): SaleReturn {
            /** @var SaleReturn $saleReturn */
            $saleReturn = SaleReturn::query()
                ->lockForUpdate()
                ->with(['items.batch' => fn (Relation $q) => $q->lockForUpdate()])
                ->findOrFail($saleReturn->id);

            $this->validateSaleReturnCanBeCompleted($saleReturn);

            $this->addStockToBatches($saleReturn);

            $saleReturn->forceFill([
                'status' => ReturnStatusEnum::Completed,
                'note' => $data->note ?? $saleReturn->note,
            ])->save();

            return $saleReturn->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateSaleReturnCanBeCompleted(SaleReturn $saleReturn): void
    {
        if ($saleReturn->status !== ReturnStatusEnum::Pending) {
            throw new StateTransitionException(
                $saleReturn->status->value,
                'Completed'
            );
        }

        if ($saleReturn->items()->count() === 0) {
            throw new InvalidOperationException(
                'complete',
                'SaleReturn',
                'Sale return cannot be completed without items'
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function addStockToBatches(SaleReturn $saleReturn): void
    {
        foreach ($saleReturn->items as $item) {
            $batch = $item->batch()->lockForUpdate()->first();

            if ($batch === null) {
                continue;
            }

            $previousQuantity = $batch->quantity;

            $batch->forceFill(['quantity' => $batch->quantity + $item->quantity])->save();

            $this->recordStockMovement->handle(new RecordStockMovementData(
                warehouse_id: $saleReturn->warehouse_id,
                product_id: $item->product_id,
                type: StockMovementTypeEnum::In,
                quantity: $item->quantity,
                previous_quantity: $previousQuantity,
                current_quantity: $previousQuantity + $item->quantity,
                reference_type: SaleReturn::class,
                reference_id: $saleReturn->id,
                batch_id: $batch->id,
                user_id: $saleReturn->user_id,
                note: 'Sale return completed - stock returned',
            ));
        }
    }
}
