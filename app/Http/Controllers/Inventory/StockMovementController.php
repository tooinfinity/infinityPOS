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
        /** @var array{search?: string|null, type?: string|null, sort?: string|null, direction?: string|null} $filters */
        $filters = request()->only(['search', 'sort', 'direction']);
        $perPage = request()->integer('per_page');

        return Inertia::render('inventory/stock-movements/index', [
            'movements' => StockMovement::query()
                ->paginateWithFilters($filters, $perPage),
            'warehouses' => Warehouse::query()->select('id', 'name')->get(),
            'products' => Product::query()->select('id', 'name', 'sku')->get(),
            'filters' => $filters,
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
