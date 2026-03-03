<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Shared\RecalculatePaymentSummary;
use App\Enums\PaymentStateEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @param  Sale|SaleReturn|Purchase|PurchaseReturn  $payable
 */
final readonly class UnvoidPayment
{
    public function __construct(private RecalculatePaymentSummary $recalculatePaymentSummary) {}

    /**
     * @throws Throwable
     */
    public function handle(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment): Payment {
            $this->validatePaymentCanBeUnvoided($payment);

            $payment->forceFill([
                'status' => PaymentStateEnum::Active,
                'voided_by' => null,
                'voided_at' => null,
                'void_reason' => null,
            ])->save();

            $this->recalculateSummaryIfNeeded($payment);

            return $payment->refresh();
        });
    }

    /**
     * @throws StateTransitionException
     */
    private function validatePaymentCanBeUnvoided(Payment $payment): void
    {
        if (! $payment->canBeUnvoided()) {
            throw new StateTransitionException(
                $payment->status->value,
                'Active'
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
