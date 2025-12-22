<?php

declare(strict_types=1);

namespace App\Http\Controllers\Taxes;

use App\Actions\Taxes\CreateTax;
use App\Actions\Taxes\DeleteTax;
use App\Actions\Taxes\UpdateTax;
use App\Data\Taxes\CreateTaxData;
use App\Data\Taxes\TaxData;
use App\Data\Taxes\UpdateTaxData;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class TaxController
{
    public function index(): Response
    {
        $taxes = Tax::query()
            ->with('creator')
            ->latest()
            ->paginate(50);

        return Inertia::render('taxes/index', [
            'taxes' => TaxData::collect($taxes),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('taxes/create');
    }

    public function store(CreateTaxData $data, CreateTax $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('taxes.index');
    }

    public function show(Tax $tax): Response
    {
        $tax->load('creator');

        return Inertia::render('taxes/show', [
            'tax' => TaxData::from($tax),
        ]);
    }

    public function edit(Tax $tax): Response
    {
        return Inertia::render('taxes/edit', [
            'tax' => TaxData::from($tax),
        ]);
    }

    public function update(UpdateTaxData $data, Tax $tax, UpdateTax $action): RedirectResponse
    {
        $action->handle($tax, $data);

        return back();
    }

    public function destroy(Tax $tax, DeleteTax $action): RedirectResponse
    {
        $action->handle($tax);

        return to_route('taxes.index');
    }
}
