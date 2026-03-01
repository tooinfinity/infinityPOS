<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Shared\CalculatePaymentStatus;
use App\Actions\StockMovement\RecordStockMovement;
use App\Data\Sale\CompleteSaleData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\SaleStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteSale
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
        private CalculatePaymentStatus $calculatePaymentStatus,
        private ValidateSaleCompletion $validateSaleCompletion,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, ?CompleteSaleData $data = null): Sale
    {
        return DB::transaction(function () use ($sale, $data): Sale {
            /** @var Sale $sale */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with(['items', 'items.batch' => fn (Relation $q) => $q->lockForUpdate()])
                ->findOrFail($sale->id);

            $this->validateSaleCompletion->handle($sale);

            $this->deductStock($sale);

            $paymentCalculation = $this->calculatePaymentStatus->handle($sale->total_amount, $sale->paid_amount);

            $note = $data instanceof CompleteSaleData ? ($data->note ?? $sale->note) : $sale->note;

            $sale->forceFill([
                'status' => SaleStatusEnum::Completed,
                'payment_status' => $paymentCalculation->paymentStatus,
                'note' => $note,
            ])->save();

            return $sale->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function deductStock(Sale $sale): void
    {
        $batchIds = $sale->items
            ->pluck('batch_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($batchIds === []) {
            return;
        }

        /** @var Collection<int, Batch> $batches */
        $batches = Batch::query()
            ->lockForUpdate()
            ->whereIn('id', $batchIds)
            ->get()
            ->keyBy('id');

        $this->validateStockAvailability($sale, $batches);

        foreach ($sale->items as $item) {
            if ($item->batch_id === null) {
                continue;
            }

            /** @var Batch $batch */
            $batch = $batches->get($item->batch_id);

            $previousQuantity = $batch->quantity;

            $batch->forceFill(['quantity' => $batch->quantity - $item->quantity])->save();

            $this->recordStockMovement->handle(new RecordStockMovementData(
                warehouse_id: $sale->warehouse_id,
                product_id: $item->product_id,
                type: StockMovementTypeEnum::Out,
                quantity: $item->quantity,
                previous_quantity: $previousQuantity,
                current_quantity: $previousQuantity - $item->quantity,
                reference_type: Sale::class,
                reference_id: $sale->id,
                batch_id: $batch->id,
                user_id: $sale->user_id,
                note: 'Sale completed - stock out',
            ));
        }
    }

    /**
     * @param  Collection<int, Batch>  $batches
     *
     * @throws Throwable
     */
    private function validateStockAvailability(Sale $sale, Collection $batches): void
    {
        /** @var array<int, int> $requiredQuantities */
        $requiredQuantities = [];

        foreach ($sale->items as $item) {
            if ($item->batch_id === null) {
                continue;
            }

            $requiredQuantities[$item->batch_id] = ($requiredQuantities[$item->batch_id] ?? 0) + $item->quantity;
        }

        foreach ($requiredQuantities as $batchId => $requiredQuantity) {
            /** @var Batch $batch */
            $batch = $batches->get($batchId);

            $newQuantity = $batch->quantity - $requiredQuantity;

            if ($newQuantity < 0) {
                throw new InsufficientStockException(
                    required: $requiredQuantity,
                    available: $batch->quantity,
                    batchId: $batchId
                );
            }
        }
    }
}
