<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Data\Payment\PaymentCalculation;
use App\Enums\PaymentStatusEnum;

final readonly class CalculatePaymentStatus
{
    public function handle(int $totalAmount, int $paidAmount): PaymentCalculation
    {
        $paymentStatus = match (true) {
            $paidAmount >= $totalAmount => PaymentStatusEnum::Paid,
            $paidAmount > 0 => PaymentStatusEnum::Partial,
            default => PaymentStatusEnum::Unpaid,
        };

        $changeAmount = max(0, $paidAmount - $totalAmount);
        $dueAmount = max(0, $totalAmount - $paidAmount);

        return new PaymentCalculation(
            paymentStatus: $paymentStatus,
            changeAmount: $changeAmount,
            dueAmount: $dueAmount,
        );
    }
}
