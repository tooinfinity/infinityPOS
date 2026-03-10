<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\RecordPayment;
use App\Data\Payment\PaymentData;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class SalePaymentController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        Sale $sale,
        PaymentData $data,
        RecordPayment $action,
    ): RedirectResponse {
        $action->handle($sale, $data);

        return to_route('sales.show', $sale)
            ->with('success', 'Payment recorded successfully.');
    }
}
