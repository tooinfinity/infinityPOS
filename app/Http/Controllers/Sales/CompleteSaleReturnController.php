<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\SaleReturn\CompleteSaleReturn;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class CompleteSaleReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(SaleReturn $saleReturn, CompleteSaleReturn $action): RedirectResponse
    {
        $action->handle($saleReturn);

        return to_route('sale-returns.show', $saleReturn)
            ->with('success', "Return {$saleReturn->reference_no} completed. Stock restored.");
    }
}
