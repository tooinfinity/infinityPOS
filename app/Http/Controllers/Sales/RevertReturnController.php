<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\SaleReturn\RevertSaleReturn as RevertSaleReturnAction;
use App\Data\SaleReturn\RevertSaleReturnData;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class RevertReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(SaleReturn $return, RevertSaleReturnAction $revertSaleReturn): RedirectResponse
    {
        $revertSaleReturn->handle($return, new RevertSaleReturnData());

        return to_route('returns.show', $return);
    }
}
