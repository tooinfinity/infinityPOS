<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Stock\ValidateStockForNewSale;
use App\Data\Sale\CreateSaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateSale
{
    public function __construct(
        private ValidateStockForNewSale $validateStockForNewSale,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateSaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $this->validateStockForNewSale->handle($data->items, $data->warehouse_id);

            $totalAmount = $data->items->toCollection()->reduce(fn (int $total, SaleItemData $item): int => $total + ($item->quantity * $item->unit_price), 0);

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
}
