<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\ApplyPaymentSummary;
use App\Actions\Stock\DeductSaleStock;
use App\Data\Sale\CreateSaleData;
use App\Data\Sale\QuickSaleData;
use App\Enums\PaymentStateEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use Throwable;

final readonly class QuickSale
{
    public function __construct(
        private CreateSale $createSale,
        private DeductSaleStock $deductSaleStock,
        private ApplyPaymentSummary $applyPaymentSummary,
        private GenerateReferenceNo $generateReferenceNo,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(QuickSaleData $data): Sale
    {
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

        $createSaleData = new CreateSaleData(
            customer_id: $data->customer_id,
            warehouse_id: $data->warehouse_id,
            user_id: $data->user_id,
            sale_date: $data->sale_date,
            note: $data->note,
            items: $data->items,
            paid_amount: $data->paid_amount,
        );

        $sale = $this->createSale->handle($createSaleData, SaleStatusEnum::Completed);

        if ($data->paid_amount > 0) {
            $this->recordPayment($sale, $data);

            $this->applyPaymentSummary->handle($sale, $data->paid_amount, preserveExistingPaidAmount: true);
        }

        $sale->load('items');
        $this->deductSaleStock->handle($sale, 'Quick sale - stock out', validateAvailability: false);

        return $sale->refresh();
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
