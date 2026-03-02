<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;

final readonly class RecalculatePaymentSummary
{
    public function __construct(
        private ApplyPaymentSummary $applyPaymentSummary,
    ) {}

    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable, bool $fromRefunds = false): void
    {
        $newPaidAmount = (int) Payment::query()
            ->activeForPayable($payable::class, $payable->id)
            ->lockForUpdate()
            ->sum('amount');

        if ($fromRefunds) {
            $newPaidAmount = abs($newPaidAmount);
        }

        $this->applyPaymentSummary->handle($payable, $newPaidAmount, capPaidAmount: true);
    }
}
