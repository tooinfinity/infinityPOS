<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\SaleReturn\CompleteSaleReturn as CompleteSaleReturnAction;
use App\Data\SaleReturn\CompleteSaleReturnData;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CompleteReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(CompleteSaleReturnAction $completeSaleReturn, SaleReturn $return): RedirectResponse
    {
        $completeSaleReturn->handle($return, new CompleteSaleReturnData());

        return to_route('returns.show', $return);
    }
}
