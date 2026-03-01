<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\GenerateReferenceNo;
use App\Data\SaleReturn\RefundSaleReturnData;
use App\Enums\PaymentStateEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\RefundNotAllowedException;
use App\Models\Payment;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessSaleReturnRefund
{
    public function __construct(private GenerateReferenceNo $generateReferenceNo) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, RefundSaleReturnData $data): Payment
    {
        return DB::transaction(function () use ($saleReturn, $data): Payment {
            /** @var SaleReturn $saleReturn */
            $saleReturn = SaleReturn::query()
                ->lockForUpdate()
                ->findOrFail($saleReturn->id);

            $this->validateRefund($saleReturn, $data->amount);

            $payment = Payment::query()->forceCreate([
                'payment_method_id' => $data->payment_method_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('PAY-SAL-REFUND', Payment::class),
                'payable_type' => SaleReturn::class,
                'payable_id' => $saleReturn->id,
                'amount' => -$data->amount,
                'payment_date' => $data->payment_date,
                'note' => $data->note,
                'status' => PaymentStateEnum::Active,
            ]);

            $this->updatePaymentStatus($saleReturn);

            return $payment->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateRefund(SaleReturn $saleReturn, int $amount): void
    {
        throw_if($saleReturn->status !== ReturnStatusEnum::Completed, RefundNotAllowedException::class, 'sale return', 'Sale return must be completed before issuing a refund.');

        throw_if($amount <= 0, RefundNotAllowedException::class, 'sale return', 'Refund amount must be greater than zero.');

        $cumulativeRefunds = (int) $saleReturn->payments()
            ->refunds()
            ->sum('amount');

        $remainingRefundable = $saleReturn->total_amount + $cumulativeRefunds;

        throw_if($amount > $remainingRefundable, RefundNotAllowedException::class, 'sale return', "Refund amount exceeds remaining refundable amount. Maximum: $remainingRefundable");
    }

    private function updatePaymentStatus(SaleReturn $saleReturn): void
    {
        $saleReturn->refresh();

        $totalRefunds = (int) $saleReturn->payments()
            ->refunds()
            ->sum('amount');

        $paymentStatus = match (true) {
            abs($totalRefunds) >= $saleReturn->total_amount => PaymentStatusEnum::Paid,
            $totalRefunds < 0 => PaymentStatusEnum::Partial,
            default => PaymentStatusEnum::Unpaid,
        };

        $saleReturn->forceFill([
            'paid_amount' => abs($totalRefunds),
            'payment_status' => $paymentStatus,
        ])->save();
    }
}
