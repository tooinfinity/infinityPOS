<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Data\SaleReturn\RefundSaleReturnData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\Payment;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final readonly class ProcessSaleReturnRefund
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, RefundSaleReturnData $data): Payment
    {
        return DB::transaction(function () use ($saleReturn, $data): Payment {
            $this->validateRefund($saleReturn, $data->amount);

            $payment = Payment::query()->forceCreate([
                'payment_method_id' => $data->payment_method_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo(),
                'payable_type' => SaleReturn::class,
                'payable_id' => $saleReturn->id,
                'amount' => -$data->amount,
                'payment_date' => $data->payment_date,
                'note' => $data->note,
            ]);

            $this->updatePaymentStatus($saleReturn);

            return $payment->refresh();
        });
    }

    private function validateRefund(SaleReturn $saleReturn, int $amount): void
    {
        throw_if($saleReturn->status !== ReturnStatusEnum::Completed, RuntimeException::class, 'Sale return must be completed before issuing a refund.');

        throw_if($amount <= 0, RuntimeException::class, 'Refund amount must be greater than zero.');

        $cumulativeRefunds = (int) $saleReturn->payments()
            ->where('amount', '<', 0)
            ->sum('amount');

        $remainingRefundable = $saleReturn->total_amount + $cumulativeRefunds;

        throw_if($amount > $remainingRefundable, RuntimeException::class, "Refund amount exceeds remaining refundable amount. Maximum: $remainingRefundable");
    }

    private function updatePaymentStatus(SaleReturn $saleReturn): void
    {
        $saleReturn->refresh();

        $totalRefunds = (int) $saleReturn->payments()
            ->where('amount', '<', 0)
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

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'REFUND-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Payment::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
