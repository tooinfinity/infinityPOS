<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchase\CreatePurchase;
use App\Actions\Purchase\DeletePurchase;
use App\Actions\Purchase\UpdatePurchase;
use App\Data\Purchase\PurchaseData;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class PurchaseController
{
    public function index(): Response
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'warehouse', 'user'])
            ->withDueAmount()
            ->latest()
            ->paginate(25);

        return Inertia::render('purchases/index', [
            'purchases' => $purchases,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('purchases/create', [
            'suppliers' => Supplier::query()->select('id', 'name', 'company_name')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
            'products' => Product::query()
                ->with('unit')
                ->select('id', 'name', 'sku', 'cost_price', 'unit_id')
                ->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(PurchaseData $data, CreatePurchase $action): RedirectResponse
    {
        $purchase = $action->handle($data);

        return to_route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->reference_no} created successfully.");
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load([
            'items.product.unit',
            'items.batch',
            'supplier',
            'warehouse',
            'user',
            'payments.paymentMethod',
        ]);

        return Inertia::render('purchases/show', [
            'purchase' => $purchase,
        ]);
    }

    public function edit(Purchase $purchase): Response
    {
        $purchase->load(['items.product']);

        return Inertia::render('purchases/edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::query()->select('id', 'name', 'company_name')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
            'products' => Product::query()
                ->with('unit')
                ->select('id', 'name', 'sku', 'cost_price', 'unit_id')
                ->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Purchase $purchase,
        PurchaseData $data,
        UpdatePurchase $action,
    ): RedirectResponse {
        $action->handle($purchase, $data);

        return to_route('purchases.show', $purchase)
            ->with('success', 'Purchase updated successfully.');
    }

    /**
     * @throws Throwable
     */
    public function destroy(Purchase $purchase, DeletePurchase $action): RedirectResponse
    {
        $action->handle($purchase);

        return to_route('purchases.index')
            ->with('success', 'Purchase deleted.');
    }
}
