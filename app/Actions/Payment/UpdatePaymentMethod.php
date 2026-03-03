<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Data\Payment\UpdatePaymentMethodData;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdatePaymentMethod
{
    /**
     * @throws Throwable
     */
    public function handle(PaymentMethod $paymentMethod, UpdatePaymentMethodData $data): PaymentMethod
    {
        return DB::transaction(static function () use ($paymentMethod, $data): PaymentMethod {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                $updateData['name'] = $data->name;
            }

            if (! $data->code instanceof Optional) {
                $updateData['code'] = $data->code;
            }

            if (! $data->is_active instanceof Optional) {
                $updateData['is_active'] = $data->is_active;
            }

            $paymentMethod->update($updateData);

            return $paymentMethod->refresh();
        });
    }
}
