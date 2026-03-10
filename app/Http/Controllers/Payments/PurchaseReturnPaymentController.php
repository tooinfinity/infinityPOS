<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\RecordPayment;
use App\Data\Payment\PaymentData;
use App\Models\PurchaseReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class PurchaseReturnPaymentController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        PurchaseReturn $purchaseReturn,
        PaymentData $data,
        RecordPayment $action,
    ): RedirectResponse {
        $action->handle($purchaseReturn, $data);

        return to_route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Refund recorded successfully.');
    }
}
