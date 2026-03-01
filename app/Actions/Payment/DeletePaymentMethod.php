<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Exceptions\InvalidOperationException;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeletePaymentMethod
{
    /**
     * @throws Throwable
     */
    public function handle(PaymentMethod $paymentMethod): bool
    {
        return DB::transaction(static function () use ($paymentMethod): bool {
            throw_if($paymentMethod->payments()->count() > 0, InvalidOperationException::class, 'delete', 'PaymentMethod', 'Cannot delete payment method with associated payments.');

            return (bool) $paymentMethod->delete();
        });
    }
}
