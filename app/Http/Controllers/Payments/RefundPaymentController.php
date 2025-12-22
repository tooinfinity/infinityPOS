<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\RefundPayment;
use App\Data\Payments\RefundPaymentData;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class RefundPaymentController
{
    /**
     * Refund a payment (partial or full).
     *
     * @throws Throwable
     */
    public function __invoke(RefundPaymentData $data, RefundPayment $action): RedirectResponse
    {
        try {
            $action->handle($data);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
