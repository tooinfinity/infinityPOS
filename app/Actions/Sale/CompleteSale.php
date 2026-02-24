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
        $this->validateStockAvailability($sale);

        foreach ($sale->items as $item) {
            if ($item->batch_id === null) {
                continue;
            }

            /** @var Batch $batch */
            $batch = Batch::query()
                ->lockForUpdate()
                ->find($item->batch_id);

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
                created_at: null,
            ));
        }
    }

    /**
     * @throws Throwable
     */
    private function validateStockAvailability(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            if ($item->batch_id === null) {
                continue;
            }
            /** @var Batch $batch */
            $batch = Batch::query()
                ->lockForUpdate()
                ->find($item->batch_id);

            $newQuantity = $batch->quantity - $item->quantity;

            throw_if($newQuantity < 0, RuntimeException::class,
                "Insufficient stock in batch. Available: $batch->quantity, Required: $item->quantity"
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
