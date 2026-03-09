<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchase\OrderPurchase;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class OrderPurchaseController
{
    /**
     * @throws Throwable
     */
    public function __invoke(Purchase $purchase, OrderPurchase $action): RedirectResponse
    {
        $action->handle($purchase);

        return to_route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->reference_no} marked as ordered.");
    }
}
