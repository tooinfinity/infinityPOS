<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Data\StockMovementData;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StockMovementController
{
    public function index(): Response
    {
        $movements = StockMovement::query()
            ->with(['product', 'store', 'creator', 'source'])
            ->latest()
            ->paginate(50);

        return Inertia::render('inventory/movements/index', [
            'movements' => StockMovementData::collect($movements),
        ]);
    }

    // TODO: Fix Me Later
    // @codeCoverageIgnoreStart
    public function show(Product $product, ?Store $store = null): Response
    {
        $query = StockMovement::query()
            ->where('product_id', $product->id)
            ->with(['store', 'creator', 'source']);

        if ($store instanceof Store) {

            $query->where('store_id', $store->id);

        }

        $movements = $query->latest()->paginate(50);

        return Inertia::render('inventory/movements/show', [
            'product' => $product,
            'store' => $store,
            'movements' => StockMovementData::collect($movements),
        ]);
    }

    // @codeCoverageIgnoreEnd
}
