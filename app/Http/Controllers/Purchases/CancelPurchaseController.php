<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\CancelPurchase;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CancelPurchaseController
{
    /**
     * @throws Throwable
     */
    public function __invoke(Purchase $purchase, CancelPurchase $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($purchase, (int) $userId);

        return back();
    }
}
