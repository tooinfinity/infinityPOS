<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\RecalculatePaymentSummary;
use App\Data\Payment\RecordPaymentData;
use App\Enums\PaymentStateEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @param  Sale|SaleReturn|Purchase|PurchaseReturn  $payable
 */
final readonly class RecordPayment
{
    public function __construct(
        private RecalculatePaymentSummary $recalculatePaymentSummary,
        private GenerateReferenceNo $generateReferenceNo,
        private ValidatePayableCanAcceptPayment $validatePayableCanAcceptPayment,
        private ValidatePaymentAmount $validatePaymentAmount,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable, RecordPaymentData $data): Payment
    {
        return DB::transaction(function () use ($payable, $data): Payment {
            /** @var Sale|SaleReturn|Purchase|PurchaseReturn $payable */
            $payable = match ($payable::class) {
                Sale::class => Sale::query()->lockForUpdate()->findOrFail($payable->id),
                SaleReturn::class => SaleReturn::query()->lockForUpdate()->findOrFail($payable->id),
                Purchase::class => Purchase::query()->lockForUpdate()->findOrFail($payable->id),
                PurchaseReturn::class => PurchaseReturn::query()->lockForUpdate()->findOrFail($payable->id),
            };

            $this->validatePaymentMethod($data->payment_method_id);

            $this->validatePayableCanAcceptPayment->handle($payable);

            $this->validatePaymentAmount->handle($payable, $data->amount);

            $payment = Payment::query()->forceCreate([
                'payment_method_id' => $data->payment_method_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('PAY', Payment::class),
                'payable_type' => $payable::class,
                'payable_id' => $payable->id,
                'amount' => $data->amount,
                'payment_date' => $data->payment_date,
                'note' => $data->note,
                'status' => PaymentStateEnum::Active,
            ]);

            $this->recalculatePaymentSummary->handle($payable);

            return $payment->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validatePaymentMethod(int $paymentMethodId): void
    {
        $paymentMethod = PaymentMethod::query()->find($paymentMethodId);

        throw_if($paymentMethod === null || ! $paymentMethod->is_active, InvalidPaymentMethodException::class, $paymentMethodId);
    }
}
