<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchase\CancelPurchase;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class CancelPurchaseController
{
    /**
     * @throws Throwable
     */
    public function __invoke(Purchase $purchase, CancelPurchase $action): RedirectResponse
    {
        $action->handle($purchase);

        return to_route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->reference_no} cancelled.");
    }
}
