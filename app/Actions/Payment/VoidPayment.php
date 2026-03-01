<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Shared\UpdatePaymentStatus;
use App\Data\Payment\VoidPaymentData;
use App\Enums\PaymentStateEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class VoidPayment
{
    public function __construct(private UpdatePaymentStatus $updatePaymentStatus) {}

    /**
     * @throws Throwable
     */
    public function handle(Payment $payment, VoidPaymentData $data, int $userId): Payment
    {
        return DB::transaction(function () use ($payment, $data, $userId): Payment {
            $this->validatePaymentCanBeVoided($payment);

            $payment->forceFill([
                'status' => PaymentStateEnum::Voided,
                'voided_by' => $userId,
                'voided_at' => now(),
                'void_reason' => $data->void_reason,
            ])->save();

            $payable = $payment->payable;

            if ($payable instanceof Sale || $payable instanceof SaleReturn || $payable instanceof Purchase || $payable instanceof PurchaseReturn) {
                $this->updatePaymentStatus->handle($payable);
            }

            return $payment->refresh();
        });
    }

    /**
     * @throws StateTransitionException
     */
    private function validatePaymentCanBeVoided(Payment $payment): void
    {
        if (! $payment->canBeVoided()) {
            throw new StateTransitionException(
                $payment->status->value,
                'Voided'
            );
        }
    }
}
