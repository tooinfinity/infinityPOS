<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\Sale\CompleteSaleData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CompleteSale
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, ?CompleteSaleData $data = null): Sale
    {
        return DB::transaction(function () use ($sale, $data): Sale {
            $sale->loadMissing('items.batch');

            $this->validateSaleCanBeCompleted($sale);

            $this->deductStock($sale);

            $paymentStatus = $this->calculatePaymentStatus($sale);

            $note = $data instanceof CompleteSaleData ? ($data->note ?? $sale->note) : $sale->note;

            $sale->forceFill([
                'status' => SaleStatusEnum::Completed,
                'payment_status' => $paymentStatus,
                'note' => $note,
            ])->save();

            return $sale->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateSaleCanBeCompleted(Sale $sale): void
    {
        if (! $sale->status->canTransitionTo(SaleStatusEnum::Completed)) {
            throw new RuntimeException(
                "Sale cannot be completed. Current status: {$sale->status->value}"
            );
        }

        throw_if($sale->items->isEmpty(), RuntimeException::class, 'Sale cannot be completed without items');
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

            throw_if(
                $newQuantity < 0,
                RuntimeException::class,
                "Insufficient stock in batch. Available: {$batch->quantity}, Required: {$requiredQuantity}"
            );
        }
    }

    private function calculatePaymentStatus(Sale $sale): PaymentStatusEnum
    {
        if ($sale->paid_amount >= $sale->total_amount) {
            return PaymentStatusEnum::Paid;
        }

        if ($sale->paid_amount > 0) {
            return PaymentStatusEnum::Partial;
        }

        return PaymentStatusEnum::Unpaid;
    }
}
