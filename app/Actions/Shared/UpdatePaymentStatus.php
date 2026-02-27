<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Enums\PaymentStateEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;

final readonly class UpdatePaymentStatus
{
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
    {
        $newPaidAmount = Payment::query()
            ->where('payable_type', $payable::class)
            ->where('payable_id', $payable->id)
            ->where('status', PaymentStateEnum::Active)
            ->lockForUpdate()
            ->sum('amount');

        $totalAmount = $payable->total_amount;

        $paymentStatus = match (true) {
            $newPaidAmount >= $totalAmount => PaymentStatusEnum::Paid,
            $newPaidAmount > 0 => PaymentStatusEnum::Partial,
            default => PaymentStatusEnum::Unpaid,
        };

        $updateData = [
            'paid_amount' => min($newPaidAmount, $totalAmount),
            'payment_status' => $paymentStatus,
        ];

        if ($payable instanceof Sale) {
            if ($newPaidAmount > $totalAmount) {
                $updateData['change_amount'] = $newPaidAmount - $totalAmount;
            } else {
                $updateData['change_amount'] = 0;
            }
        }

        $payable->forceFill($updateData)->save();
    }
}
