<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\PaymentStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;

final readonly class UpdatePaymentStatus
{
    public function handle(Sale|Purchase|SaleReturn|PurchaseReturn $payable): void
    {
        $paidAmount = $payable->activePayments()->sum('amount');

        $status = match (true) {
            $paidAmount <= 0 => PaymentStatusEnum::Unpaid,
            $paidAmount < $payable->total_amount => PaymentStatusEnum::Partial,
            default => PaymentStatusEnum::Paid,
        };

        $payable->forceFill([
            'paid_amount' => $paidAmount,
            'payment_status' => $status,
        ])->save();
    }
}
