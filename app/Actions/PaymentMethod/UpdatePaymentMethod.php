<?php

declare(strict_types=1);

namespace App\Actions\PaymentMethod;

use App\Data\Payment\PaymentMethodData;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdatePaymentMethod
{
    /**
     * @throws Throwable
     */
    public function handle(PaymentMethod $method, PaymentMethodData $data): PaymentMethod
    {
        /** @var PaymentMethod $result */
        $result = DB::transaction(static function () use ($method, $data): PaymentMethod {
            $updateData = [
                'name' => $data->name ?? $method->name,
                'code' => $data->code ?? $method->code,
                'is_active' => $data->is_active ?? $method->is_active,
            ];
            $method->update($updateData);

            return $method->refresh();
        });

        return $result;
    }
}
