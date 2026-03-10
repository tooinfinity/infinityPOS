<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\RecordPayment;
use App\Data\Payment\PaymentData;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class SaleReturnPaymentController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        SaleReturn $saleReturn,
        PaymentData $data,
        RecordPayment $action,
    ): RedirectResponse {
        $action->handle($saleReturn, $data);

        return to_route('sale-returns.show', $saleReturn)
            ->with('success', 'Refund recorded successfully.');
    }
}
