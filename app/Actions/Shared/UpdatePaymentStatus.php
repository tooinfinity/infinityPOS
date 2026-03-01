<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;

final readonly class UpdatePaymentStatus
{
    public function __construct(
        private CalculatePaymentStatus $calculatePaymentStatus,
    ) {}

    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
    {
        $newPaidAmount = (int) Payment::query()
            ->forPayable($payable::class, $payable->id)
            ->active()
            ->lockForUpdate()
            ->sum('amount');

        $totalAmount = $payable->total_amount;

        $paymentCalculation = $this->calculatePaymentStatus->handle($totalAmount, $newPaidAmount);

        $updateData = [
            'paid_amount' => $totalAmount - $paymentCalculation->dueAmount,
            'payment_status' => $paymentCalculation->paymentStatus,
        ];

        if ($payable instanceof Sale) {
            $updateData['change_amount'] = $paymentCalculation->changeAmount;
        }

        $payable->forceFill($updateData)->save();
    }
}
