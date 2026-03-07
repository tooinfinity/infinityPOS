<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sale\CompleteSale as CompleteSaleAction;
use App\Data\Sale\CompleteSaleData;
use App\Http\Requests\Sale\CompleteSaleRequest;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class CompleteSaleController
{
    /**
     * @throws Throwable
     */
    public function __invoke(CompleteSaleRequest $request, CompleteSaleAction $completeSale, Sale $sale): RedirectResponse
    {
        $completeSale->handle($sale, CompleteSaleData::from($request->validated()));

        return to_route('sales.show', $sale);
    }
}
