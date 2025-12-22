<?php

declare(strict_types=1);

namespace App\Http\Controllers\Units;

use App\Actions\Units\CreateUnit;
use App\Actions\Units\DeleteUnit;
use App\Actions\Units\UpdateUnit;
use App\Data\Units\CreateUnitData;
use App\Data\Units\UnitData;
use App\Data\Units\UpdateUnitData;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UnitController
{
    public function index(): Response
    {
        $units = Unit::query()
            ->with('creator')
            ->latest()
            ->paginate(50);

        return Inertia::render('units/index', [
            'units' => UnitData::collect($units),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('units/create');
    }

    public function store(CreateUnitData $data, CreateUnit $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('units.index');
    }

    public function show(Unit $unit): Response
    {
        $unit->load('creator');

        return Inertia::render('units/show', [
            'unit' => UnitData::from($unit),
        ]);
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('units/edit', [
            'unit' => UnitData::from($unit),
        ]);
    }

    public function update(UpdateUnitData $data, Unit $unit, UpdateUnit $action): RedirectResponse
    {
        $action->handle($unit, $data);

        return back();
    }

    public function destroy(Unit $unit, DeleteUnit $action): RedirectResponse
    {
        $action->handle($unit);

        return to_route('units.index');
    }
}
