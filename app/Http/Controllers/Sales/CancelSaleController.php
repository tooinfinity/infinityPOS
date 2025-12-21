<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\CancelSale;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CancelSaleController
{
    /**
     * @throws Throwable
     */
    public function __invoke(Sale $sale, CancelSale $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($sale, (int) $userId);

        return back();
    }
}
