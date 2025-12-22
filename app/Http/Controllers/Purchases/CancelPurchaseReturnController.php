<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\CancelPurchaseReturn;
use App\Models\PurchaseReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CancelPurchaseReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(PurchaseReturn $purchaseReturn, CancelPurchaseReturn $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($purchaseReturn, (int) $userId);

        return back();
    }
}
