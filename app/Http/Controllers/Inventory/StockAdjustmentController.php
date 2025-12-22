<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\Inventory\AdjustStock;
use App\Data\Inventory\AdjustStockData;
use App\Data\StockMovementData;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class StockAdjustmentController
{
    public function index(): Response
    {
        $adjustments = StockMovement::query()
            ->whereNull('source_type')
            ->whereNull('source_id')
            ->with(['product', 'store', 'creator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('inventory/adjustment/index', [
            'adjustments' => StockMovementData::collect($adjustments),
        ]);
    }

    public function create(): Response
    {
        $products = Product::query()->where('is_active', true)->latest()->get();
        $stores = Store::query()->latest()->get();

        return Inertia::render('inventory/adjustment/create', [
            'products' => $products,
            'stores' => $stores,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(AdjustStockData $data, AdjustStock $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('inventory.adjustments.index');
    }
}
