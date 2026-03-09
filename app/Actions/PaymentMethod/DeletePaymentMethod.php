<?php

declare(strict_types=1);

namespace App\Actions\PaymentMethod;

use App\Exceptions\InvalidOperationException;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeletePaymentMethod
{
    /**
     * @throws Throwable
     */
    public function handle(PaymentMethod $method): bool
    {
        /** @var bool $result */
        $result = DB::transaction(static function () use ($method): bool {
            if ($method->payments()->exists()) {
                throw new InvalidOperationException(
                    'delete',
                    'PaymentMethod',
                    'Cannot delete a payment method with associated payments.'
                );
            }

            return (bool) $method->delete();
        });

        return $result;
    }
}
