<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\CreatePurchase;
use App\Actions\Purchases\DeletePurchase;
use App\Actions\Purchases\UpdatePurchase;
use App\Data\PurchaseData;
use App\Data\Purchases\CreatePurchaseData;
use App\Data\Purchases\UpdatePurchaseData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Throwable;

final readonly class PurchaseController
{
    public function index(): Response
    {
        $purchases = Purchase::with(['supplier', 'store', 'creator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('purchase/index', [
            'purchases' => PurchaseData::collect($purchases),
            'statuses' => PurchaseStatusEnum::toArray(),
        ]);
    }

    public function create(): Response
    {
        $suppliers = Supplier::query()->latest()->get();
        $stores = Store::query()->latest()->get();

        return Inertia::render('purchase/create', [
            'suppliers' => $suppliers,
            'stores' => $stores,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(CreatePurchaseData $data, CreatePurchase $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('purchases.index');
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load(['supplier', 'store', 'creator', 'items.product', 'payments']);

        return Inertia::render('purchase/show', [
            'purchase' => PurchaseData::from($purchase),
        ]);
    }

    public function edit(Purchase $purchase): Response
    {
        $purchase->load(['supplier', 'store', 'items.product']);
        $suppliers = Supplier::query()->latest()->get();
        $stores = Store::query()->latest()->get();

        return Inertia::render('purchase/edit', [
            'purchase' => PurchaseData::from($purchase),
            'suppliers' => $suppliers,
            'stores' => $stores,
        ]);
    }

    public function update(UpdatePurchaseData $data, Purchase $purchase, UpdatePurchase $action): RedirectResponse
    {
        $action->handle($purchase, $data);

        return back();
    }

    /**
     * @throws Throwable
     */
    public function destroy(Purchase $purchase, DeletePurchase $action): RedirectResponse
    {
        try {
            $action->handle($purchase);

            return to_route('purchases.index');
        } catch (InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['message' => $invalidArgumentException->getMessage()]);
        }
    }
}
