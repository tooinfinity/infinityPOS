<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\ProcessPurchaseReturn;
use App\Data\PurchaseReturnData;
use App\Data\Purchases\ProcessPurchaseReturnData;
use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class PurchaseReturnController
{
    public function index(): Response
    {
        $purchaseReturns = PurchaseReturn::with(['purchase', 'supplier', 'store', 'creator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('purchase-return/index', [
            'purchase_returns' => PurchaseReturnData::collect($purchaseReturns),
            'statuses' => PurchaseReturnStatusEnum::toArray(),
        ]);
    }

    public function create(Purchase $purchase): Response
    {
        $purchase->load(['supplier', 'store', 'items.product']);

        return Inertia::render('purchase-return/create', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(ProcessPurchaseReturnData $data, ProcessPurchaseReturn $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('purchase-returns.index');
    }

    public function show(PurchaseReturn $purchaseReturn): Response
    {
        $purchaseReturn->load(['purchase', 'supplier', 'store', 'creator', 'items.product', 'payments']);

        return Inertia::render('purchase-return/show', [
            'purchase_return' => PurchaseReturnData::from($purchaseReturn),
        ]);
    }
}
