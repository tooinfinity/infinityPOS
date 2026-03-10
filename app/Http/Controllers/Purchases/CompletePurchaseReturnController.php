<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\PurchaseReturn\CompletePurchaseReturn;
use App\Models\PurchaseReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class CompletePurchaseReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        PurchaseReturn $purchaseReturn,
        CompletePurchaseReturn $action,
    ): RedirectResponse {
        $action->handle($purchaseReturn);

        return to_route('purchase-returns.show', $purchaseReturn)
            ->with('success', "Return {$purchaseReturn->reference_no} completed. Stock deducted.");
    }
}
