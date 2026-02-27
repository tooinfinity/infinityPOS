<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Data\Payment\VoidPaymentData;
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
final readonly class VoidPayment
{
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
                $this->updatePayablePaymentStatus($payable);
            }

            return $payment->refresh();
        });
    }

    private function validatePaymentCanBeVoided(Payment $payment): void
    {
        if (! $payment->canBeVoided()) {
            throw new RuntimeException(
                'Payment cannot be voided. Current status: '.$payment->status->value
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
