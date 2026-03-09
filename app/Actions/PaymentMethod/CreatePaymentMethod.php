<?php

declare(strict_types=1);

namespace App\Actions\PaymentMethod;

use App\Data\Payment\PaymentMethodData;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePaymentMethod
{
    /**
     * @throws Throwable
     */
    public function handle(PaymentMethodData $data): PaymentMethod
    {
        /** @var PaymentMethod $method */
        $method = DB::transaction(
            static fn (): PaymentMethod => PaymentMethod::query()->forceCreate([
                'name' => $data->name,
                'code' => $data->code,
                'is_active' => $data->is_active,
            ])->refresh()
        );

        return $method;
    }
}
