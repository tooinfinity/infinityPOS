<?php

declare(strict_types=1);

namespace App\Http\Controllers\Suppliers;

use App\Actions\Suppliers\CreateSupplier;
use App\Actions\Suppliers\DeleteSupplier;
use App\Actions\Suppliers\UpdateSupplier;
use App\Data\Suppliers\CreateSupplierData;
use App\Data\Suppliers\SupplierData;
use App\Data\Suppliers\UpdateSupplierData;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class SupplierController
{
    public function index(): Response
    {
        $suppliers = Supplier::query()
            ->with('creator')
            ->latest()
            ->paginate(50);

        return Inertia::render('suppliers/index', [
            'suppliers' => SupplierData::collect($suppliers),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('suppliers/create');
    }

    public function store(CreateSupplierData $data, CreateSupplier $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('suppliers.index');
    }

    public function show(Supplier $supplier): Response
    {
        $supplier->load('creator');

        return Inertia::render('suppliers/show', [
            'supplier' => SupplierData::from($supplier),
        ]);
    }

    public function edit(Supplier $supplier): Response
    {
        return Inertia::render('suppliers/edit', [
            'supplier' => SupplierData::from($supplier),
        ]);
    }

    public function update(UpdateSupplierData $data, Supplier $supplier, UpdateSupplier $action): RedirectResponse
    {
        $action->handle($supplier, $data);

        return back();
    }

    public function destroy(Supplier $supplier, DeleteSupplier $action): RedirectResponse
    {
        $action->handle($supplier);

        return to_route('suppliers.index');
    }
}
