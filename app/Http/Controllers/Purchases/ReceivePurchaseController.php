<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchase\ReceivePurchase;
use App\Data\Purchase\ReceivePurchaseData;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class ReceivePurchaseController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        Purchase $purchase,
        ReceivePurchaseData $data,
        ReceivePurchase $action,
    ): RedirectResponse {
        $action->handle($purchase, $data);

        return to_route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->reference_no} received. Stock updated.");
    }
}
