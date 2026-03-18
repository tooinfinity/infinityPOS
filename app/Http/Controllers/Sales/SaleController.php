<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sale\CreateSale;
use App\Actions\Sale\DeleteSale;
use App\Actions\Sale\UpdateSale;
use App\Data\Sale\SaleData;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class SaleController
{
    public function index(Request $request): Response
    {
        /**
         * @var array{
         *     search?: string|null,
         *     status?: string|null,
         *     payment_status?: string|null,
         *     sort?: string|null,
         *     direction?: string|null
         * } $filters
         */
        $filters = $request->only(['search', 'status', 'payment_status', 'sort', 'direction']);
        /** @var int $perPage */
        $perPage = $request->input('per_page', 25);

        $sales = Sale::query()
            ->paginateWithFilters($filters, $perPage);

        return Inertia::render('sales/index', [
            'sales' => $sales,
            'customers' => Customer::query()->select('id', 'name')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name')->get(),
            'products' => Product::query()->forSaleForm(),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('sales/create', [
            'customers' => Customer::query()->select('id', 'name')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name')->get(),
            'products' => Product::query()->forSaleForm(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(SaleData $data, CreateSale $action): RedirectResponse
    {
        $sale = $action->handle($data);

        return to_route('sales.show', $sale)
            ->with('success', "Sale {$sale->reference_no} created successfully.");
    }

    public function show(Sale $sale): Response
    {
        $sale->load([
            'items.product.unit',
            'items.batch',
            'customer',
            'warehouse',
            'user',
            'payments.paymentMethod',
        ]);

        return Inertia::render('sales/show', [
            'sale' => $sale,
            'customers' => Customer::query()->select('id', 'name')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name')->get(),
            'products' => Product::query()->forSaleForm(),
            'payment_methods' => PaymentMethod::query()->select('id', 'name', 'code')->get(),
        ]);
    }

    public function edit(Sale $sale): Response
    {
        $sale->load(['items.product', 'items.batch']);

        return Inertia::render('sales/edit', [
            'sale' => $sale,
            'customers' => Customer::query()->select('id', 'name')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name')->get(),
            'products' => Product::query()->forSaleForm(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(Sale $sale, SaleData $data, UpdateSale $action): RedirectResponse
    {
        $action->handle($sale, $data);

        return to_route('sales.show', $sale)
            ->with('success', 'Sale updated successfully.');
    }

    /**
     * @throws Throwable
     */
    public function destroy(Sale $sale, DeleteSale $action): RedirectResponse
    {
        $action->handle($sale);

        return to_route('sales.index')
            ->with('success', 'Sale deleted.');
    }
}
