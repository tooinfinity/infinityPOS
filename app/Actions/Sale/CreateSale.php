<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Data\Sale\CreateSaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class CreateSale
{
    /**
     * @throws Throwable
     */
    public function handle(CreateSaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $this->validateStockAvailability($data->items);

            $totalAmount = $this->calculateTotalAmount($data->items);

            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo(),
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
     * @param  DataCollection<int, SaleItemData>  $items
     */
    private function validateStockAvailability(DataCollection $items): void
    {
        $requiredByBatch = [];
        foreach ($items as $item) {
            $batchId = $item->batch_id;
            $requiredByBatch[$batchId]['quantity'] = ($requiredByBatch[$batchId]['quantity'] ?? 0) + $item->quantity;
            $requiredByBatch[$batchId]['product_id'] ??= $item->product_id;
        }

        $batchIds = array_keys($requiredByBatch);
        /** @var Collection<int, Batch> $batches */
        $batches = Batch::query()
            ->whereIn('id', $batchIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($requiredByBatch as $batchId => $required) {
            $batch = $batches->get($batchId);

            if ($batch === null) {
                throw new RuntimeException("Batch not found for product {$required['product_id']}");
            }

            if ($batch->quantity < $required['quantity']) {
                throw new RuntimeException(
                    "Insufficient stock in batch. Required: {$required['quantity']}, Available: $batch->quantity"
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

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'SAL-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Sale::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
