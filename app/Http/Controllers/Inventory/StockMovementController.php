<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StockMovementController
{
    /**
     * Read-only — stock movements are created by actions, never directly.
     */
    public function index(): Response
    {
        $movements = StockMovement::query()
            ->with([
                'product:id,name,sku',
                'warehouse:id,name',
                'batch:id,batch_number',
                'user:id,name',
            ])
            ->latest()
            ->paginate(25);

        return Inertia::render('inventory/stock-movements/index', [
            'movements' => $movements,
            'warehouses' => Warehouse::query()->select('id', 'name')->get(),
            'products' => Product::query()->select('id', 'name', 'sku')->get(),
        ]);
    }

    public function show(StockMovement $stockMovement): Response
    {
        $stockMovement->load([
            'product.unit',
            'warehouse',
            'batch',
            'user',
            'reference',
        ]);

        return Inertia::render('inventory/stock-movements/show', [
            'movement' => $stockMovement,
        ]);
    }
}
