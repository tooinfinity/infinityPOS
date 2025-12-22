<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\CompletePurchaseReturn;
use App\Models\PurchaseReturn;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class CompletePurchaseReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(PurchaseReturn $purchaseReturn, CompletePurchaseReturn $action): RedirectResponse
    {
        try {
            $userId = auth()->id();
            abort_if($userId === null, 401);

            $action->handle($purchaseReturn, (int) $userId);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
