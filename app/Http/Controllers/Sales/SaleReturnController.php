<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\SaleReturn\CreateSaleReturn;
use App\Actions\SaleReturn\DeleteSaleReturn;
use App\Actions\SaleReturn\ResolveReturnableQuantity;
use App\Data\SaleReturn\SaleReturnData;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class SaleReturnController
{
    public function index(): Response
    {
        $returns = SaleReturn::query()
            ->with(['sale', 'warehouse', 'user'])
            ->withDueAmount()
            ->latest()
            ->paginate(25);

        return Inertia::render('sales/returns/index', [
            'returns' => $returns,
        ]);
    }

    /**
     * Optionally pre-fill form from an existing sale.
     */
    public function create(
        ResolveReturnableQuantity $resolveReturnableQuantity,
        ?Sale $sale = null,
    ): Response {
        $returnableMap = null;

        if ($sale instanceof Sale) {
            $sale->load('items.product.unit', 'items.batch');
            $returnableMap = $resolveReturnableQuantity->handle($sale);
        }

        return Inertia::render('sales/returns/create', [
            'sale' => $sale?->load('items.product'),
            'returnableMap' => $returnableMap,
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(SaleReturnData $data, CreateSaleReturn $action): RedirectResponse
    {
        $return = $action->handle($data);

        return to_route('sale-returns.show', $return)
            ->with('success', "Sale return {$return->reference_no} created successfully.");
    }

    public function show(SaleReturn $saleReturn): Response
    {
        $saleReturn->load([
            'items.product.unit',
            'items.batch',
            'sale',
            'warehouse',
            'user',
            'payments.paymentMethod',
        ]);

        return Inertia::render('sales/returns/show', [
            'return' => $saleReturn,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(SaleReturn $saleReturn, DeleteSaleReturn $action): RedirectResponse
    {
        $action->handle($saleReturn);

        return to_route('sale-returns.index')
            ->with('success', 'Sale return deleted.');
    }
}
