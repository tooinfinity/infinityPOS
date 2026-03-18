<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\PurchaseReturn\CreatePurchaseReturn;
use App\Actions\PurchaseReturn\DeletePurchaseReturn;
use App\Actions\PurchaseReturn\ResolveReturnableQuantity;
use App\Data\PurchaseReturn\PurchaseReturnData;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class PurchaseReturnController
{
    public function index(): Response
    {
        /** @var array{search?: string|null, status?: string|null, payment_status?: string|null, sort?: string|null, direction?: string|null} $filters */
        $filters = request()->only(['search', 'status', 'payment_status', 'sort', 'direction']);
        $perPage = request()->integer('per_page');

        return Inertia::render('purchase-returns/index', [
            'purchaseReturns' => PurchaseReturn::query()
                ->paginateWithFilters($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    /**
     * Optionally pre-fill form from an existing purchase.
     */
    public function create(
        ResolveReturnableQuantity $resolveReturnableQuantity,
        ?Purchase $purchase = null,
    ): Response {
        $returnableItems = [];

        if ($purchase instanceof Purchase) {
            $purchase->load('items.product.unit', 'items.batch');
            $returnableMap = $resolveReturnableQuantity->handle($purchase);

            $returnableItems = $purchase->items
                ->filter(fn (PurchaseItem $item): bool => $returnableMap->get($item->product_id, 0) > 0)
                ->map(fn (PurchaseItem $item): array => [
                    'purchase_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'batch_number' => $item->batch?->batch_number,
                    'unit_cost' => $item->unit_cost,
                    'unit_short_name' => $item->product->unit->short_name,
                    'max_quantity' => $returnableMap->get($item->product_id, 0),
                ])
                ->values();
        }

        return Inertia::render('purchase-returns/create', [
            'purchase' => $purchase?->load('items.product'),
            'returnableItems' => $returnableItems,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(PurchaseReturnData $data, CreatePurchaseReturn $action): RedirectResponse
    {
        $return = $action->handle($data);

        return to_route('purchase-returns.show', $return)
            ->with('success', "Purchase return {$return->reference_no} created successfully.");
    }

    public function show(PurchaseReturn $purchaseReturn): Response
    {
        $purchaseReturn->load([
            'items.product.unit',
            'items.batch',
            'purchase.supplier',
            'warehouse',
            'user',
            'payments.paymentMethod',
        ]);

        return Inertia::render('purchase-returns/show', [
            'purchaseReturn' => $purchaseReturn,
            'payment_methods' => PaymentMethod::query()->select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(
        PurchaseReturn $purchaseReturn,
        DeletePurchaseReturn $action,
    ): RedirectResponse {
        $action->handle($purchaseReturn);

        return to_route('purchase-returns.index')
            ->with('success', 'Purchase return deleted.');
    }
}
