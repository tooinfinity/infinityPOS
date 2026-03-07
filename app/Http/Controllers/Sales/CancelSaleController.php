<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sale\CancelSale as CancelSaleAction;
use App\Data\Sale\CancelSaleData;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CancelSaleController
{
    /**
     * @throws Throwable
     */
    public function __invoke(StoreSaleRequest $request, CancelSaleAction $cancelSale, Sale $sale): RedirectResponse
    {
        $cancelSale->handle($sale, CancelSaleData::from($request->validated()));

        return to_route('sales.show', $sale);
    }
}
