<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Exceptions\InvalidOperationException;
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
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable, int $amount): void
    {
        throw_if($amount < 0, InvalidOperationException::class, 'record payment', 'Payment', 'Amount cannot be negative.');

        $currentPaid = Payment::sumForPayable($payable);

        if ($payable instanceof Sale) {
            $maxAllowedPayment = $payable->total_amount * 2;

            throw_if(($currentPaid + $amount) > $maxAllowedPayment, OverpaymentException::class, $amount, $maxAllowedPayment, $currentPaid);

            return;
        }

        if (($currentPaid + $amount) > $payable->total_amount) {
            throw new OverpaymentException($amount, $payable->total_amount, $currentPaid);
        }
    }
}
