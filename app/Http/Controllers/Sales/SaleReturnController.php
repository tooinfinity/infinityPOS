<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\SaleReturn\CreateSaleReturn;
use App\Actions\SaleReturn\DeleteSaleReturn;
use App\Actions\SaleReturn\ResolveReturnableQuantity;
use App\Data\SaleReturn\SaleReturnData;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class SaleReturnController
{
    public function index(): Response
    {
        $returns = SaleReturn::query()
            ->with(['sale.customer', 'warehouse', 'user'])
            ->withDueAmount()
            ->latest()
            ->paginate(25);

        return Inertia::render('sale-returns/index', [
            'saleReturns' => $returns,
            'filters' => request()->query(),
        ]);
    }

    /**
     * Optionally pre-fill form from an existing sale.
     */
    public function create(
        ResolveReturnableQuantity $resolveReturnableQuantity,
        Sale $sale,
    ): Response {
        $sale->load('items.product.unit', 'items.batch');
        $returnableMap = $resolveReturnableQuantity->handle($sale);

        $returnableItems = $sale->items
            ->filter(fn (SaleItem $item): bool => $returnableMap->get($item->product_id, 0) > 0)
            ->map(fn (SaleItem $item): array => [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku,
                'batch_number' => $item->batch?->batch_number,
                'unit_price' => $item->unit_price,
                'unit_short_name' => $item->product->unit->short_name,
                'max_quantity' => $returnableMap->get($item->product_id, 0),
            ])
            ->values();

        return Inertia::render('sale-returns/create', [
            'sale' => $sale,
            'returnableItems' => $returnableItems,
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

        return Inertia::render('sale-returns/show', [
            'saleReturn' => $saleReturn,
            'payment_methods' => PaymentMethod::query()->select('id', 'name', 'code')->get(),
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
