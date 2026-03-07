<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\UnvoidPayment as UnvoidPaymentAction;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;

final readonly class UnvoidPayment
{
    public function __construct(
        private UnvoidPaymentAction $unvoidPayment,
    ) {}

    public function __invoke(Payment $payment): RedirectResponse
    {
        $this->unvoidPayment->handle($payment);

        return back();
    }
}
