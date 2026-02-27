<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Shared\UpdatePaymentStatus;
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
    public function __construct(private UpdatePaymentStatus $updatePaymentStatus) {}

    /**
     * @throws Throwable
     */
    public function handle(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment): Payment {
            $this->validatePaymentCanBeUnvoided($payment);

            $payment->forceFill([
                'status' => \App\Enums\PaymentStateEnum::Active,
                'voided_by' => null,
                'voided_at' => null,
                'void_reason' => null,
            ])->save();

            $payable = $payment->payable;

            if ($payable instanceof Sale || $payable instanceof SaleReturn || $payable instanceof Purchase || $payable instanceof PurchaseReturn) {
                $this->updatePaymentStatus->handle($payable);
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
}
