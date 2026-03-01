<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\GenerateReferenceNo;
use App\Data\PurchaseReturn\RefundPurchaseReturnData;
use App\Enums\PaymentStateEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\RefundNotAllowedException;
use App\Models\Payment;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessPurchaseReturnRefund
{
    public function __construct(private GenerateReferenceNo $generateReferenceNo) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, RefundPurchaseReturnData $data): Payment
    {
        return DB::transaction(function () use ($purchaseReturn, $data): Payment {
            /** @var PurchaseReturn $purchaseReturn */
            $purchaseReturn = PurchaseReturn::query()
                ->lockForUpdate()
                ->findOrFail($purchaseReturn->id);
            $this->validateRefund($purchaseReturn, $data->amount);

            $payment = Payment::query()->forceCreate([
                'payment_method_id' => $data->payment_method_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('PAY-PUR-REFUND', Payment::class),
                'payable_type' => PurchaseReturn::class,
                'payable_id' => $purchaseReturn->id,
                'amount' => -$data->amount,
                'payment_date' => $data->payment_date,
                'note' => $data->note,
                'status' => PaymentStateEnum::Active,
            ]);

            $this->updatePaymentStatus($purchaseReturn);

            return $payment->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateRefund(PurchaseReturn $purchaseReturn, int $amount): void
    {
        throw_if($purchaseReturn->status !== ReturnStatusEnum::Completed, RefundNotAllowedException::class, 'purchase return', 'Purchase return must be completed before issuing a refund.');

        throw_if($amount <= 0, RefundNotAllowedException::class, 'purchase return', 'Refund amount must be greater than zero.');

        $cumulativeRefunds = (int) $purchaseReturn->payments()
            ->refunds()
            ->sum('amount');

        $remainingRefundable = $purchaseReturn->total_amount + $cumulativeRefunds;

        throw_if($amount > $remainingRefundable, RefundNotAllowedException::class, 'purchase return', "Refund amount exceeds remaining refundable amount. Maximum: $remainingRefundable");
    }

    private function updatePaymentStatus(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->refresh();

        $totalRefunds = (int) $purchaseReturn->payments()
            ->refunds()
            ->sum('amount');

        $paymentStatus = match (true) {
            abs($totalRefunds) >= $purchaseReturn->total_amount => PaymentStatusEnum::Paid,
            $totalRefunds < 0 => PaymentStatusEnum::Partial,
            default => PaymentStatusEnum::Unpaid,
        };

        $purchaseReturn->forceFill([
            'paid_amount' => abs($totalRefunds),
            'payment_status' => $paymentStatus,
        ])->save();
    }
}
