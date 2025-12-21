<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\ProcessSaleReturn;
use App\Data\SaleReturnData;
use App\Data\Sales\ProcessSaleReturnData;
use App\Enums\SaleReturnStatusEnum;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class SaleReturnController
{
    public function index(): Response
    {
        $saleReturns = SaleReturn::with(['sale', 'client', 'store', 'creator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('sale-return/index', [
            'sale_returns' => SaleReturnData::collect($saleReturns),
            'statuses' => SaleReturnStatusEnum::toArray(),
        ]);
    }

    public function create(Sale $sale): Response
    {
        $sale->load(['client', 'store', 'items.product']);

        return Inertia::render('sale-return/create', [
            'sale' => $sale,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(ProcessSaleReturnData $data, ProcessSaleReturn $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('sale-returns.index');
    }

    public function show(SaleReturn $saleReturn): Response
    {
        $saleReturn->load(['sale', 'client', 'store', 'creator', 'items.product', 'payments']);

        return Inertia::render('sale-return/show', [
            'sale_return' => SaleReturnData::from($saleReturn),
        ]);
    }
}
