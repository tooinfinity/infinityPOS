<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\Inventory\CreateStockTransfer;
use App\Data\Inventory\CreateStockTransferData;
use App\Data\StockTransferData;
use App\Enums\StockTransferStatusEnum;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class StockTransferController
{
    public function index(): Response
    {
        $transfers = StockTransfer::with(['fromStore', 'toStore', 'creator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('inventory/stock-transfer/index', [
            'transfers' => StockTransferData::collect($transfers),
            'statuses' => StockTransferStatusEnum::toArray(),
        ]);
    }

    public function create(): Response
    {
        $stores = Store::query()->latest()->get();
        $products = Product::query()->where('is_active', true)->latest()->get();

        return Inertia::render('inventory/stock-transfer/create', [
            'stores' => $stores,
            'products' => $products,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(CreateStockTransferData $data, CreateStockTransfer $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('inventory.stock-transfers.index');
    }

    public function show(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load(['fromStore', 'toStore', 'creator', 'items.product', 'stockMovements']);

        return Inertia::render('inventory/stock-transfer/show', [
            'transfer' => StockTransferData::from($stockTransfer),
        ]);
    }
}
