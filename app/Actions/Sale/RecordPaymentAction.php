<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Data\Sale\RecordPaymentData;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * @param  Sale|SaleReturn|Purchase|PurchaseReturn  $payable
 */
final readonly class RecordPaymentAction
{
    /**
     * @throws Throwable
     */
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable, RecordPaymentData $data): Payment
    {
        return DB::transaction(function () use ($payable, $data): Payment {
            $this->validatePayable($payable);

            $payment = Payment::query()->forceCreate([
                'payment_method_id' => $data->payment_method_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo(),
                'payable_type' => $payable::class,
                'payable_id' => $payable->id,
                'amount' => $data->amount,
                'payment_date' => $data->payment_date,
                'note' => $data->note,
            ]);

            $this->updatePayablePaymentStatus($payable);

            return $payment->refresh();
        });
    }

    private function validatePayable(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
    {
        $canAcceptPayment = $this->checkCanAcceptPayment($payable);

        if (! $canAcceptPayment) {
            $payableClass = $payable::class;
            $statusValue = $this->getStatusValue($payable);
            throw new RuntimeException(
                "Cannot record payment for {$payableClass} with status: {$statusValue}"
            );
        }
    }

    private function checkCanAcceptPayment(Sale|SaleReturn|Purchase|PurchaseReturn $payable): bool
    {
        return match ($payable::class) {
            Sale::class => $payable->status === SaleStatusEnum::Completed,
            SaleReturn::class => $payable->status === ReturnStatusEnum::Completed,
            Purchase::class => $payable->status === PurchaseStatusEnum::Received,
            default => true,
        };
    }

    private function getStatusValue(Sale|SaleReturn|Purchase|PurchaseReturn $payable): string
    {
        return match ($payable::class) {
            Sale::class => $payable->status->value,
            SaleReturn::class => $payable->status->value,
            Purchase::class => $payable->status->value,
            default => $payable->status->value,
        };
    }

    private function updatePayablePaymentStatus(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
    {
        $payable->refresh();

        $newPaidAmount = $payable->payments()->sum('amount');
        $totalAmount = $payable->total_amount;

        $paymentStatus = match (true) {
            $newPaidAmount >= $totalAmount => PaymentStatusEnum::Paid,
            $newPaidAmount > 0 => PaymentStatusEnum::Partial,
            default => PaymentStatusEnum::Unpaid,
        };

        $payable->forceFill([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
        ])->save();
    }

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'PAY-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Payment::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
