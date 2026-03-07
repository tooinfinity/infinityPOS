<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payment\VoidPayment as VoidPaymentAction;
use App\Data\Payment\VoidPaymentData;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;

final readonly class VoidPayment
{
    public function __construct(
        private VoidPaymentAction $voidPayment,
    ) {}

    public function __invoke(Payment $payment): RedirectResponse
    {
        $data = new VoidPaymentData(void_reason: request('note', 'Payment voided'));
        $userId = auth()->id();

        $this->voidPayment->handle($payment, $data, $userId);

        return back();
    }
}
