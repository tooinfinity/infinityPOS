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

        $updates = [
            'paid_amount' => min($paidAmount, $payable->total_amount),
            'payment_status' => $status,
        ];

        if ($payable instanceof Sale && $payable->customer_id === null) {
            $updates['change_amount'] = max(0, $paidAmount - $payable->total_amount);
            $updates['paid_amount'] = $payable->total_amount; // never exceed total
            $updates['payment_status'] = $paidAmount >= $payable->total_amount
                ? PaymentStatusEnum::Paid
                : PaymentStatusEnum::Partial;
        }

        $payable->forceFill($updates)->save();
    }
}
