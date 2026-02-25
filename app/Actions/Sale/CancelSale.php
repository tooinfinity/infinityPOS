<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\Sale\CancelSaleData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\SaleStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CancelSale
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, CancelSaleData $data): Sale
    {
        return DB::transaction(function () use ($sale, $data): Sale {
            $this->validateSaleCanBeCancelled($sale);

            $shouldRestock = $data->restock_items && $sale->status === SaleStatusEnum::Completed;

            if ($shouldRestock) {
                /** @var Sale $sale */
                $sale = Sale::query()
                    ->lockForUpdate()
                    ->with(['items.batch' => fn (Relation $query): Relation => $query->lockForUpdate()])
                    ->findOrFail($sale->id);

                $this->restockItems($sale);
            }

            $sale->forceFill([
                'status' => SaleStatusEnum::Cancelled,
                'note' => $data->note ?? $sale->note,
            ])->save();

            return $sale->refresh();
        });
    }

    private function validateSaleCanBeCancelled(Sale $sale): void
    {
        if (! $sale->status->canTransitionTo(SaleStatusEnum::Cancelled)) {
            throw new RuntimeException(
                "Sale cannot be cancelled. Current status: {$sale->status->value}"
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function restockItems(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $batch = $item->batch;

            if ($batch === null) {
                continue;
            }

            $previousQuantity = $batch->quantity;

            $batch->forceFill(['quantity' => $batch->quantity + $item->quantity])->save();

            $this->recordStockMovement->handle(new RecordStockMovementData(
                warehouse_id: $sale->warehouse_id,
                product_id: $item->product_id,
                type: StockMovementTypeEnum::In,
                quantity: $item->quantity,
                previous_quantity: $previousQuantity,
                current_quantity: $previousQuantity + $item->quantity,
                reference_type: Sale::class,
                reference_id: $sale->id,
                batch_id: $batch->id,
                user_id: $sale->user_id,
                note: 'Sale cancelled - stock returned',
            ));
        }
    }
}
