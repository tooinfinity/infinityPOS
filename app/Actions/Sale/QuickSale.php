<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\CalculatePaymentStatus;
use App\Actions\Shared\CalculateSaleTotal;
use App\Actions\Stock\DeductSaleStock;
use App\Actions\Stock\ValidateStockForNewSale;
use App\Data\Sale\QuickSaleData;
use App\Enums\PaymentStateEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class QuickSale
{
    public function __construct(
        private CreateSaleItems $createSaleItems,
        private DeductSaleStock $deductSaleStock,
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
                    ->exists();

                if (! $paymentMethodExists) {
                    throw new InvalidPaymentMethodException(
                        $data->payment_method_id,
                        'Payment method is not active or does not exist'
                    );
                }
            }

            $this->validateStockForNewSale->handle($data->items, $data->warehouse_id);

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

            $this->createSaleItems->handle($sale->id, $data->items);

            $sale->load('items');

            if ($data->paid_amount > 0) {
                $this->recordPayment($sale, $data);
            }

            $this->deductSaleStock->handle($sale, 'Quick sale - stock out', validateAvailability: false);

            return $sale->refresh();
        });
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
