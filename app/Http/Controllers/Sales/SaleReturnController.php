<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Http\Requests\SaleReturn\StoreSaleReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Throwable;

final readonly class SaleReturnController
{
    public function index(): Response
    {
        //
    }

    public function create(?Sale $sale = null): Response
    {
        //
    }

    /**
     * @throws Throwable
     */
    public function store(StoreSaleReturnRequest $request): RedirectResponse
    {
        //
    }

    public function show(SaleReturn $return): Response
    {
        //
    }

    public function edit(SaleReturn $return): Response
    {
        //
    }

    /**
     * @throws Throwable
     */
    public function destroy(SaleReturn $return): RedirectResponse
    {
        //
    }
}
