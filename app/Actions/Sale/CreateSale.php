<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\CalculatePaymentStatus;
use App\Actions\Shared\RecalculateParentTotal;
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
        private RecalculateParentTotal $recalculateTotal,
        private CalculatePaymentStatus $calculatePaymentStatus,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateSaleData $data, SaleStatusEnum $status = SaleStatusEnum::Pending): Sale
    {
        return DB::transaction(function () use ($data, $status): Sale {
            $this->validateStockForNewSale->handle($data->items, $data->warehouse_id);

            $sale = Sale::query()->forceCreate([
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('SAL', Sale::class),
                'status' => $status,
                'sale_date' => $data->sale_date,
                'total_amount' => 0,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'note' => $data->note,
            ]);

            $this->createSaleItems->handle($sale->id, $data->items);

            $this->recalculateTotal->handle($sale);

            $sale = $sale->refresh();

            $totalAmount = $sale->total_amount;

            $paymentCalculation = $this->calculatePaymentStatus->handle($totalAmount, $data->paid_amount ?? 0);

            $sale->forceFill([
                'total_amount' => $totalAmount,
                'paid_amount' => min($data->paid_amount ?? 0, $totalAmount),
                'change_amount' => $paymentCalculation->changeAmount,
            ])->save();

            return $sale->refresh();
        });
    }
}
