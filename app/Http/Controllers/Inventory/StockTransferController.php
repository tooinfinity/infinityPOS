<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\StockTransfer\CreateStockTransfer;
use App\Actions\StockTransfer\DeleteStockTransfer;
use App\Actions\StockTransfer\UpdateStockTransfer;
use App\Data\StockTransfer\StockTransferData;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class StockTransferController
{
    public function index(): Response
    {
        $transfers = StockTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse', 'user'])
            ->withCount('items')
            ->latest()
            ->paginate(25);

        return Inertia::render('inventory/stock-transfers/index', [
            'transfers' => $transfers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('inventory/stock-transfers/create', [
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
            'products' => Product::query()
                ->with(['unit', 'batches' => fn (Relation $q) => $q->where('quantity', '>', 0)->with('warehouse')])
                ->withStockQuantity()
                ->select('id', 'name', 'sku', 'unit_id')
                ->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StockTransferData $data, CreateStockTransfer $action): RedirectResponse
    {
        $transfer = $action->handle($data);

        return to_route('stock-transfers.show', $transfer)
            ->with('success', "Transfer {$transfer->reference_no} created successfully.");
    }

    public function show(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load([
            'items.product.unit',
            'items.batch',
            'fromWarehouse',
            'toWarehouse',
            'user',
            'stockMovements',
        ]);

        return Inertia::render('inventory/stock-transfers/show', [
            'transfer' => $stockTransfer,
        ]);
    }

    public function edit(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load(['items.product', 'items.batch']);

        return Inertia::render('inventory/stock-transfers/edit', [
            'transfer' => $stockTransfer,
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
            'products' => Product::query()
                ->with(['unit', 'batches' => fn (Relation $q) => $q->where('quantity', '>', 0)->with('warehouse')])
                ->withStockQuantity()
                ->select('id', 'name', 'sku', 'unit_id')
                ->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        StockTransfer $stockTransfer,
        StockTransferData $data,
        UpdateStockTransfer $action,
    ): RedirectResponse {
        $action->handle($stockTransfer, $data);

        return to_route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Transfer updated successfully.');
    }

    /**
     * @throws Throwable
     */
    public function destroy(StockTransfer $stockTransfer, DeleteStockTransfer $action): RedirectResponse
    {
        $action->handle($stockTransfer);

        return to_route('stock-transfers.index')
            ->with('success', 'Transfer deleted.');
    }
}
