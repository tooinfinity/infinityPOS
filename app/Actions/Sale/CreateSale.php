<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\CalculateSaleTotal;
use App\Actions\Stock\ValidateStockForNewSale;
use App\Data\Sale\CreateSaleData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateSale
{
    public function __construct(
        private CreateSaleItems $createSaleItems,
        private ValidateStockForNewSale $validateStockForNewSale,
        private GenerateReferenceNo $generateReferenceNo,
        private CalculateSaleTotal $calculateSaleTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateSaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $this->validateStockForNewSale->handle($data->items, $data->warehouse_id);

            $totalAmount = $this->calculateSaleTotal->handle($data->items);

            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('SAL', Sale::class),
                'status' => SaleStatusEnum::Pending,
                'sale_date' => $data->sale_date,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'note' => $data->note,
            ]);

            $this->createSaleItems->handle($sale->id, $data->items);

            return $sale->refresh();
        });
    }
}
