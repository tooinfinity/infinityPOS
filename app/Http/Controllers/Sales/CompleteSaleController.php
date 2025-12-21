<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\CompleteSale;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final readonly class CompleteSaleController
{
    /**
     * @throws Throwable
     */
    public function __invoke(Sale $sale, CompleteSale $action): RedirectResponse
    {
        try {
            $userId = auth()->id();
            abort_if($userId === null, 401);

            $action->handle($sale, (int) $userId);

            return back();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
