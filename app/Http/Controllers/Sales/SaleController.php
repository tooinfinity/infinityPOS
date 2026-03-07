<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Sale\UpdateSaleRequest;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

final readonly class SaleController
{
    public function index(): Response
    {
        //
    }

    public function create(): Response
    {
        //
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        //
    }

    public function show(Sale $sale): Response
    {
        //
    }

    public function edit(Sale $sale): Response
    {
        //
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        //
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        //
    }
}
