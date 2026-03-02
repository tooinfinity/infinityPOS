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
        private ApplyPaymentSummary $applyPaymentSummary,
    ) {}

    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
    {
        $newPaidAmount = (int) Payment::query()
            ->activeForPayable($payable::class, $payable->id)
            ->lockForUpdate()
            ->sum('amount');

        $this->applyPaymentSummary->handle($payable, $newPaidAmount, capPaidAmount: true);
    }
}
