<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\VoidPayment;
use App\Data\Payment\VoidPaymentData;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class VoidPaymentController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        Payment $payment,
        VoidPaymentData $data,
        VoidPayment $action,
    ): RedirectResponse {
        $action->handle($payment, $data->void_reason);

        return back()
            ->with('success', "Payment #{$payment->reference_no} voided.");
    }
}
