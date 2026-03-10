<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Unit\CreateUnit;
use App\Actions\Unit\DeleteUnit;
use App\Actions\Unit\UpdateUnit;
use App\Data\Unit\UnitData;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class UnitController
{
    public function index(): Response
    {
        return Inertia::render('products/units/index', [
            'units' => Unit::withInactive()
                ->withCount('products')
                ->latest()
                ->paginate(25),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('products/units/create');
    }

    /**
     * @throws Throwable
     */
    public function store(UnitData $data, CreateUnit $action): RedirectResponse
    {
        $unit = $action->handle($data);

        return to_route('units.show', $unit)
            ->with('success', "Unit '{$unit->name}' created.");
    }

    public function show(Unit $unit): Response
    {
        $unit->loadCount('products');

        return Inertia::render('products/units/show', [
            'unit' => $unit,
        ]);
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('products/units/edit', [
            'unit' => $unit,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(Unit $unit, UnitData $data, UpdateUnit $action): RedirectResponse
    {
        $action->handle($unit, $data);

        return to_route('units.index')
            ->with('success', "Unit '{$unit->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(Unit $unit, DeleteUnit $action): RedirectResponse
    {
        $action->handle($unit);

        return to_route('units.index')
            ->with('success', 'Unit deleted.');
    }
}
