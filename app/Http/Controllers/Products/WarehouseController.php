<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Warehouse\CreateWarehouse;
use App\Actions\Warehouse\DeleteWarehouse;
use App\Actions\Warehouse\UpdateWarehouse;
use App\Data\Warehouse\WarehouseData;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class WarehouseController
{
    public function index(): Response
    {
        return Inertia::render('products/warehouses/index', [
            'warehouses' => Warehouse::withInactive()
                ->withCount(['batches', 'purchases', 'sales'])
                ->latest()
                ->paginate(25),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('products/warehouses/create');
    }

    /**
     * @throws Throwable
     */
    public function store(WarehouseData $data, CreateWarehouse $action): RedirectResponse
    {
        $warehouse = $action->handle($data);

        return to_route('warehouses.index')
            ->with('success', "Warehouse '{$warehouse->name}' created.");
    }

    public function show(Warehouse $warehouse): Response
    {
        $warehouse->loadCount([
            'batches',
            'purchases',
            'sales',
            'stockMovements',
            'transfersFrom',
            'transfersTo',
        ]);

        return Inertia::render('products/warehouses/show', [
            'warehouse' => $warehouse,
        ]);
    }

    public function edit(Warehouse $warehouse): Response
    {
        return Inertia::render('products/warehouses/edit', [
            'warehouse' => $warehouse,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Warehouse $warehouse,
        WarehouseData $data,
        UpdateWarehouse $action,
    ): RedirectResponse {
        $action->handle($warehouse, $data);

        return to_route('warehouses.index')
            ->with('success', "Warehouse '{$warehouse->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(Warehouse $warehouse, DeleteWarehouse $action): RedirectResponse
    {
        $action->handle($warehouse);

        return to_route('warehouses.index')
            ->with('success', 'Warehouse deleted.');
    }
}
