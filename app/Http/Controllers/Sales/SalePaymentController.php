<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\ProcessSalePayment;
use App\Data\Sales\ProcessSalePaymentData;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;

final readonly class SalePaymentController
{
    public function store(ProcessSalePaymentData $data, Sale $sale, ProcessSalePayment $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle(
            sale: $sale,
            amount: $data->amount,
            method: $data->method,
            reference: $data->reference,
            notes: $data->notes,
            userId: (int) $userId,
        );

        return back();
    }
}
