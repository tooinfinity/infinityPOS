<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\RecordPayment;
use App\Data\Payment\PaymentData;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class PurchasePaymentController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        Purchase $purchase,
        PaymentData $data,
        RecordPayment $action,
    ): RedirectResponse {
        $action->handle($purchase, $data);

        return to_route('purchases.show', $purchase)
            ->with('success', 'Payment recorded successfully.');
    }
}
