<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sale\CancelSale;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class CancelSaleController
{
    /**
     * @throws Throwable
     */
    public function __invoke(Sale $sale, CancelSale $action): RedirectResponse
    {
        $action->handle($sale);

        return to_route('sales.show', $sale)
            ->with('success', "Sale {$sale->reference_no} cancelled.");
    }
}
