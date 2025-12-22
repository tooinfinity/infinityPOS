<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\ReceivePurchase;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class ReceivePurchaseController
{
    /**
     * @throws Throwable
     */
    public function __invoke(Purchase $purchase, ReceivePurchase $action): RedirectResponse
    {
        try {
            $userId = auth()->id();
            abort_if($userId === null, 401);

            $action->handle($purchase, (int) $userId);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
