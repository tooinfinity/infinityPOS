<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\PaymentStateEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * @param  Sale|SaleReturn|Purchase|PurchaseReturn  $payable
 */
final readonly class UnvoidPayment
{
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

            $payable = $payment->payable;

            if ($payable instanceof Sale || $payable instanceof SaleReturn || $payable instanceof Purchase || $payable instanceof PurchaseReturn) {
                $this->updatePayablePaymentStatus($payable);
            }

            return $payment->refresh();
        });
    }

    private function validatePaymentCanBeUnvoided(Payment $payment): void
    {
        if (! $payment->canBeUnvoided()) {
            throw new RuntimeException(
                'Payment cannot be unvoided. Current status: '.$payment->status->value
            );
        }
    }

    private function updatePayablePaymentStatus(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
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

        if ($payable instanceof Sale && $newPaidAmount > $totalAmount) {
            $updateData['change_amount'] = $newPaidAmount - $totalAmount;
        } elseif ($payable instanceof Sale) {
            $updateData['change_amount'] = 0;
        }

        $payable->forceFill($updateData)->save();
    }
}
