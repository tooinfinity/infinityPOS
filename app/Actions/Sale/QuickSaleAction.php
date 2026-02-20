<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\Sale\QuickSaleData;
use App\Data\Sale\SaleItemData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class QuickSaleAction
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(QuickSaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $this->validateStockAvailability($data->items);

            $totalAmount = $this->calculateTotalAmount($data->items);

            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo(),
                'status' => SaleStatusEnum::Completed,
                'sale_date' => $data->sale_date,
                'total_amount' => $totalAmount,
                'paid_amount' => min($data->paid_amount, $totalAmount),
                'change_amount' => max(0, $data->paid_amount - $totalAmount),
                'payment_status' => $data->paid_amount >= $totalAmount
                    ? PaymentStatusEnum::Paid
                    : ($data->paid_amount > 0 ? PaymentStatusEnum::Partial : PaymentStatusEnum::Unpaid),
                'note' => $data->note,
            ]);

            foreach ($data->items as $item) {
                SaleItem::query()->forceCreate([
                    'sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'unit_cost' => $item->unit_cost,
                    'subtotal' => $item->quantity * $item->unit_price,
                ]);

                $this->deductStock($sale, $item);
            }

            if ($data->paid_amount > 0) {
                $this->recordPayment($sale, $data);
            }

            return $sale->refresh();
        });
    }

    /**
     * @param  DataCollection<int, SaleItemData>  $items
     */
    private function validateStockAvailability(DataCollection $items): void
    {
        foreach ($items as $item) {
            $batch = Batch::query()->find($item->batch_id);

            if ($batch === null) {
                throw new RuntimeException("Batch not found for product {$item->product_id}");
            }

            if ($batch->quantity < $item->quantity) {
                throw new RuntimeException(
                    "Insufficient stock in batch. Required: {$item->quantity}, Available: {$batch->quantity}"
                );
            }
        }
    }

    /**
     * @param  DataCollection<int, SaleItemData>  $items
     */
    private function calculateTotalAmount(DataCollection $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item->quantity * $item->unit_price;
        }

        return $total;
    }

    /**
     * @throws Throwable
     */
    private function deductStock(Sale $sale, SaleItemData $item): void
    {
        /** @var Batch $batch */
        $batch = Batch::query()->find($item->batch_id);

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
            note: 'Quick sale - stock out',
            created_at: null,
        ));
    }

    private function recordPayment(Sale $sale, QuickSaleData $data): void
    {
        $paidAmount = min($data->paid_amount, $sale->total_amount);

        Payment::query()->forceCreate([
            'payment_method_id' => $data->payment_method_id,
            'user_id' => $data->user_id,
            'reference_no' => $this->generatePaymentReferenceNo(),
            'payable_type' => Sale::class,
            'payable_id' => $sale->id,
            'amount' => $paidAmount,
            'payment_date' => $data->sale_date,
            'note' => 'Quick sale payment',
        ]);
    }

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'SAL-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Sale::query()->where('reference_no', $reference)->exists());

        return $reference;
    }

    private function generatePaymentReferenceNo(): string
    {
        do {
            $reference = 'PAY-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Payment::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
