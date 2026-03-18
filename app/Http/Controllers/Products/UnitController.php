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
        /** @var array{search?: string|null, sort?: string|null, direction?: string|null} $filters */
        $filters = request()->only(['search', 'sort', 'direction']);
        $perPage = request()->integer('per_page');

        return Inertia::render('units/index', [
            'units' => Unit::withInactive()
                ->paginateWithFilters($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('units/create');
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

        return Inertia::render('units/show', [
            'unit' => $unit,
        ]);
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('units/edit', [
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
