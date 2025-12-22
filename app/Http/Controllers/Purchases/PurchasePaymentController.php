<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\ProcessPurchasePayment;
use App\Data\Sales\ProcessSalePaymentData;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;

final readonly class PurchasePaymentController
{
    public function store(ProcessSalePaymentData $data, Purchase $purchase, ProcessPurchasePayment $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle(
            purchase: $purchase,
            amount: $data->amount,
            method: $data->method,
            reference: $data->reference,
            notes: $data->notes,
            userId: (int) $userId,
        );

        return back();
    }
}
