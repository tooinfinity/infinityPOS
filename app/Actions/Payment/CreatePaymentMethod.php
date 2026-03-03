<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Data\Payment\CreatePaymentMethodData;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePaymentMethod
{
    /**
     * @throws Throwable
     */
    public function handle(CreatePaymentMethodData $data): PaymentMethod
    {
        return DB::transaction(static fn (): PaymentMethod => PaymentMethod::query()->forceCreate([
            'name' => $data->name,
            'code' => $data->code,
            'is_active' => $data->is_active,
        ])->refresh());
    }
}
