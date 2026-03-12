<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Supplier\CreateSupplier;
use App\Actions\Supplier\DeleteSupplier;
use App\Actions\Supplier\UpdateSupplier;
use App\Data\Supplier\SupplierData;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class SupplierController
{
    public function index(): Response
    {
        return Inertia::render('suppliers/index', [
            'suppliers' => Supplier::withInactive()
                ->withCount('purchases')
                ->latest()
                ->paginate(25),
            'filters' => request()->query(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(SupplierData $data, CreateSupplier $action): RedirectResponse
    {
        $supplier = $action->handle($data);

        return to_route('suppliers.show', $supplier)
            ->with('success', "Supplier '{$supplier->name}' created.");
    }

    public function show(Supplier $supplier): Response
    {
        $supplier->loadCount('purchases');

        $supplier->load([
            'purchases' => fn (Relation $q) => $q
                ->latest()
                ->limit(10),
        ]);

        return Inertia::render('suppliers/show', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Supplier $supplier,
        SupplierData $data,
        UpdateSupplier $action,
    ): RedirectResponse {
        $action->handle($supplier, $data);

        return to_route('suppliers.show', $supplier)
            ->with('success', "Supplier '{$supplier->name}' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(Supplier $supplier, DeleteSupplier $action): RedirectResponse
    {
        $action->handle($supplier);

        return to_route('suppliers.index')
            ->with('success', 'Supplier deleted.');
    }
}
