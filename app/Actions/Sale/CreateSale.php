<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Payment\UpdatePaymentStatus;
use App\Actions\Stock\DeductStock;
use App\Data\Sale\SaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class CreateSale
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
        private DeductStock $deductStock,
        private UpdatePaymentStatus $updatePaymentStatus,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('SAL', Sale::class),
                'status' => $data->status,
                'sale_date' => $data->sale_date,
                'total_amount' => $data->total_amount,
                'paid_amount' => 0,
                'payment_status' => \App\Enums\PaymentStatusEnum::Unpaid,
                'note' => $data->note,
            ]);

            $this->createItems($sale, $data->items);

            if ($data->status === SaleStatusEnum::Completed) {
                $this->deductStockForSale($sale);
            }

            if ($data->paid_amount > 0) {
                $sale->forceFill(['paid_amount' => $data->paid_amount])->save();
                $this->updatePaymentStatus->handle($sale);
            }

            return $sale->load(['items.product', 'items.batch', 'customer', 'warehouse']);
        });
    }

    /**
     * @param  DataCollection<int, SaleItemData>  $items
     */
    private function createItems(Sale $sale, DataCollection $items): void
    {
        $items->each(function (SaleItemData $itemData) use ($sale): void {
            $sale->items()->forceCreate([
                'product_id' => $itemData->product_id,
                'batch_id' => $itemData->batch_id,
                'quantity' => $itemData->quantity,
                'unit_price' => $itemData->unit_price,
                'unit_cost' => $itemData->unit_cost,
                'subtotal' => $itemData->unit_price * $itemData->quantity,
            ]);
        });
    }

    /**
     * @throws InsufficientStockException
     */
    private function deductStockForSale(Sale $sale): void
    {
        $sale->load('items.batch');

        $sale->items->each(function ($item) use ($sale): void {
            if ($item->batch instanceof Batch) {
                $this->deductStock->handle(
                    batch: $item->batch,
                    quantity: $item->quantity,
                    reference: $sale,
                    note: "Sale {$sale->reference_no}",
                );
            }
        });
    }
}
