<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Shared\RecalculatePaymentSummary;
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
    public function __construct(private RecalculatePaymentSummary $recalculatePaymentSummary) {}

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

            $this->recalculateSummaryIfNeeded($payment);

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

    private function recalculateSummaryIfNeeded(Payment $payment): void
    {
        $payable = $payment->payable()->lockForUpdate()->first();

        if ($payable !== null && in_array($payable::class, [Sale::class, SaleReturn::class, Purchase::class, PurchaseReturn::class])) {
            $this->recalculatePaymentSummary->handle($payable);
        }
    }
}
