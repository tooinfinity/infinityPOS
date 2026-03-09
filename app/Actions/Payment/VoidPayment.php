<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\PaymentStateEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class VoidPayment
{
    public function __construct(
        private UpdatePaymentStatus $updatePaymentStatus,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Payment $payment, string $voidReason): Payment
    {
        /** @var Payment $result */
        $result = DB::transaction(function () use ($payment, $voidReason): Payment {
            if (! $payment->canBeVoided()) {
                throw new InvalidOperationException(
                    'void',
                    'Payment',
                    "Payment #$payment->id is already voided."
                );
            }

            $payment->forceFill([
                'status' => PaymentStateEnum::Voided,
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_reason' => $voidReason,
            ])->save();

            /** @var Sale|Purchase|SaleReturn|PurchaseReturn $payable */
            $payable = $payment->payable()->lockForUpdate()->firstOrFail();

            $this->updatePaymentStatus->handle($payable);

            return $payment->refresh()->load('paymentMethod', 'voidedBy');
        });

        return $result;
    }
}
