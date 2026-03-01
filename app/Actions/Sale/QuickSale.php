<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\CalculatePaymentStatus;
use App\Actions\Shared\CalculateSaleTotal;
use App\Actions\Stock\ValidateStockForNewSale;
use App\Actions\StockMovement\RecordStockMovement;
use App\Data\Sale\QuickSaleData;
use App\Data\Sale\SaleItemData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\PaymentStateEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class QuickSale
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
        private ValidateStockForNewSale $validateStockForNewSale,
        private GenerateReferenceNo $generateReferenceNo,
        private CalculateSaleTotal $calculateSaleTotal,
        private CalculatePaymentStatus $calculatePaymentStatus,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(QuickSaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            if ($data->paid_amount > 0) {
                $paymentMethodExists = PaymentMethod::query()
                    ->where('id', $data->payment_method_id)
                    ->where('is_active', true)
                    ->exists();

                if (! $paymentMethodExists) {
                    throw new InvalidPaymentMethodException(
                        $data->payment_method_id,
                        'Payment method is not active or does not exist'
                    );
                }
            }

            $this->validateStockForNewSale->handle($data->items, $data->warehouse_id);

            $requiredByBatch = $this->calculateRequiredQuantities($data->items);

            $itemsArray = $data->items->toArray();
            $batchIds = array_unique(array_column($itemsArray, 'batch_id'));

            /** @var Collection<int, Batch> $batches */
            $batches = Batch::query()
                ->whereIn('id', $batchIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalAmount = $this->calculateSaleTotal->handle($data->items);

            $paymentCalculation = $this->calculatePaymentStatus->handle($totalAmount, $data->paid_amount);

            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('SAL', Sale::class),
                'status' => SaleStatusEnum::Completed,
                'sale_date' => $data->sale_date,
                'total_amount' => $totalAmount,
                'paid_amount' => min($data->paid_amount, $totalAmount),
                'change_amount' => $paymentCalculation->changeAmount,
                'payment_status' => $paymentCalculation->paymentStatus,
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
            }

            if ($data->paid_amount > 0) {
                $this->recordPayment($sale, $data);
            }

            $this->deductStockFromBatches($sale, $batches, $requiredByBatch);

            return $sale->refresh();
        });
    }

    /**
     * @param  DataCollection<int, SaleItemData>  $items
     * @return array<int, array{quantity: int, product_id: int}>
     */
    private function calculateRequiredQuantities(DataCollection $items): array
    {
        $requiredByBatch = [];
        foreach ($items as $item) {
            if (! isset($requiredByBatch[$item->batch_id])) {
                $requiredByBatch[$item->batch_id] = [
                    'quantity' => 0,
                    'product_id' => $item->product_id,
                ];
            }
            $requiredByBatch[$item->batch_id]['quantity'] += $item->quantity;
        }

        return $requiredByBatch;
    }

    /**
     * Combined validation and deduction in single locked loop
     *
     * @param  array<int, array{quantity: int, product_id: int}>  $requiredByBatch
     * @param  Collection<int, Batch>  $batches
     *
     * @throws Throwable
     */
    private function deductStockFromBatches(Sale $sale, Collection $batches, array $requiredByBatch): void
    {
        foreach ($requiredByBatch as $batchId => $required) {
            /** @var Batch $batch */
            $batch = $batches->get($batchId);

            $previousQuantity = $batch->quantity;
            $newQuantity = $previousQuantity - $required['quantity'];

            $batch->forceFill(['quantity' => $newQuantity])->save();

            $this->recordStockMovement->handle(new RecordStockMovementData(
                warehouse_id: $sale->warehouse_id,
                product_id: $required['product_id'],
                type: StockMovementTypeEnum::Out,
                quantity: $required['quantity'],
                previous_quantity: $previousQuantity,
                current_quantity: $newQuantity,
                reference_type: Sale::class,
                reference_id: $sale->id,
                batch_id: $batch->id,
                user_id: $sale->user_id,
                note: 'Quick sale - stock out',
            ));
        }
    }

    private function recordPayment(Sale $sale, QuickSaleData $data): void
    {
        $paidAmount = min($data->paid_amount, $sale->total_amount);

        Payment::query()->forceCreate([
            'payment_method_id' => $data->payment_method_id,
            'user_id' => $data->user_id,
            'reference_no' => $this->generateReferenceNo->handle('PAY', Payment::class),
            'payable_type' => Sale::class,
            'payable_id' => $sale->id,
            'amount' => $paidAmount,
            'payment_date' => $data->sale_date,
            'note' => 'Quick sale payment',
            'status' => PaymentStateEnum::Active,
        ]);
    }
}
