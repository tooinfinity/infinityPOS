<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\CancelSaleReturn;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CancelSaleReturnController
{
    /**
     * @throws Throwable
     */
    public function __invoke(SaleReturn $saleReturn, CancelSaleReturn $action): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        $action->handle($saleReturn, (int) $userId);

        return back();
    }
}
