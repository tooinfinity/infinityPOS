<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\VoidPayment;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class VoidPaymentController
{
    /**
     * Void a payment (complete reversal).
     *
     * @throws Throwable
     */
    public function __invoke(Payment $payment, VoidPayment $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        /** @var string $reason */
        $reason = request()->input('reason');

        $action->handle($payment, (int) $userId, $reason);

        return back();
    }
}
