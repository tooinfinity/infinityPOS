<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\PaymentStateEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Exceptions\OverpaymentException;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Throwable;

final readonly class ValidatePaymentAmount
{
    /**
     * @throws Throwable
     */
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable, float $amount): void
    {
        throw_if($amount < 0, InvalidPaymentMethodException::class, 0, 'Payment amount cannot be negative.');

        $currentPaid = $this->getCurrentPaidAmount($payable);

        if ($payable instanceof Sale) {
            $maxAllowedPayment = $payable->total_amount * 2;

            throw_if(($currentPaid + $amount) > $maxAllowedPayment, OverpaymentException::class, $amount, $maxAllowedPayment, $currentPaid);

            return;
        }

        if (($currentPaid + $amount) > $payable->total_amount) {
            throw new OverpaymentException($amount, $payable->total_amount, $currentPaid);
        }
    }

    private function getCurrentPaidAmount(Sale|SaleReturn|Purchase|PurchaseReturn $payable): int
    {
        /** @var int $amount */
        $amount = Payment::query()
            ->where('payable_type', $payable::class)
            ->where('payable_id', $payable->id)
            ->where('status', PaymentStateEnum::Active)
            ->sum('amount');

        return $amount;
    }
}
