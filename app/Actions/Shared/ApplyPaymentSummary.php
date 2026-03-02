<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;

final readonly class ApplyPaymentSummary
{
    public function __construct(
        private CalculatePaymentStatus $calculatePaymentStatus,
    ) {}

    public function handle(
        Sale|SaleReturn|Purchase|PurchaseReturn $payable,
        int $paidAmount,
        bool $capPaidAmount = false,
    ): void {
        $paymentCalculation = $this->calculatePaymentStatus->handle(
            $payable->total_amount,
            $paidAmount
        );

        $finalPaidAmount = $capPaidAmount
            ? min($paidAmount, $payable->total_amount)
            : $paidAmount;

        $updateData = [
            'paid_amount' => $finalPaidAmount,
            'payment_status' => $paymentCalculation->paymentStatus,
        ];

        if ($payable->hasCast('change_amount')) {
            $updateData['change_amount'] = $paymentCalculation->changeAmount;
        }

        $payable->forceFill($updateData)->save();
    }
}
