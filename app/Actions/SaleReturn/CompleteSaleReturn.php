<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\StockMovement\CreateStockMovement;
use App\Data\SaleReturn\CompleteSaleReturnData;
use App\Enums\ReturnStatusEnum;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteSaleReturn
{
    public function __construct(
        private CreateStockMovement $createStockMovement,
        private ValidateSaleReturnCanBeCompleted $validateSaleReturnCanBeCompleted,
    ) {}

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

            $this->validateSaleReturnCanBeCompleted->handle($saleReturn);

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
    private function addStockToBatches(SaleReturn $saleReturn): void
    {
        foreach ($saleReturn->items as $item) {
            $batch = $item->batch()->lockForUpdate()->first();

            if ($batch === null) {
                continue;
            }

            $previousQuantity = $batch->quantity;
            $batch->forceFill(['quantity' => $previousQuantity + $item->quantity])->save();

            $this->createStockMovement->recordIn(
                $batch,
                $item->quantity,
                $previousQuantity,
                $saleReturn,
                $saleReturn->user_id,
                'Sale return completed - stock returned',
            );
        }
    }
}
