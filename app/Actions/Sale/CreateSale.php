<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\CalculateTotalFromItems;
use App\Data\Sale\CreateSaleData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CreateSale
{
    public function __construct(private CalculateTotalFromItems $calculateTotal) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateSaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $this->validateStockAvailability($data);

            $totalAmount = $this->calculateTotal->handle($data->items, 'unit_price');

            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => new GenerateReferenceNo('SAL', Sale::query())->handle(),
                'status' => SaleStatusEnum::Pending,
                'sale_date' => $data->sale_date,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
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

            return $sale->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateStockAvailability(CreateSaleData $data): void
    {
        /** @var array<int, int> $quantitiesByBatch */
        $quantitiesByBatch = [];

        foreach ($data->items as $item) {
            $batchId = $item->batch_id;
            if (! isset($quantitiesByBatch[$batchId])) {
                $quantitiesByBatch[$batchId] = 0;
            }
            $quantitiesByBatch[$batchId] += $item->quantity;
        }

        foreach ($data->items as $item) {
            $batch = Batch::query()
                ->lockForUpdate()
                ->find($item->batch_id);

            if ($batch === null) {
                throw new RuntimeException("Batch not found for product {$item->product_id}");
            }

            if ($batch->product_id !== $item->product_id) {
                throw new RuntimeException("Batch does not belong to product {$item->product_id}");
            }

            throw_if($batch->warehouse_id !== $data->warehouse_id, RuntimeException::class, "Batch is not in the sale's warehouse");
        }

        foreach ($quantitiesByBatch as $batchId => $totalQuantity) {
            /** @var Batch $batch */
            $batch = Batch::query()->lockForUpdate()->find($batchId);

            if ($batch->quantity < $totalQuantity) {
                throw new RuntimeException(
                    "Insufficient stock in batch. Required: {$totalQuantity}, Available: {$batch->quantity}"
                );
            }
        }
    }
}
