<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Batch\CreateBatch;
use App\Actions\Batch\DeleteBatch;
use App\Actions\Batch\UpdateBatch;
use App\Data\Batch\BatchData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class BatchController
{
    public function index(): Response
    {
        $batches = Batch::query()
            ->with(['product.unit', 'warehouse'])
            ->latest()
            ->paginate(25);

        return Inertia::render('batches/index', [
            'batches' => $batches,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('batches/create', [
            'products' => Product::query()->select('id', 'name', 'sku')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(BatchData $data, CreateBatch $action): RedirectResponse
    {
        $batch = $action->handle($data);

        return to_route('batches.show', $batch)
            ->with('success', "Batch '{$batch->batch_number}' created.");
    }

    public function show(Batch $batch): Response
    {
        $batch->load([
            'product.unit',
            'warehouse',
            'stockMovements' => fn (Relation $q) => $q->latest()->limit(20),
        ]);

        return Inertia::render('batches/show', [
            'batch' => $batch,
        ]);
    }

    public function edit(Batch $batch): Response
    {
        $batch->load(['product', 'warehouse']);

        return Inertia::render('batches/edit', [
            'batch' => $batch,
            'products' => Product::query()->select('id', 'name', 'sku')->get(),
            'warehouses' => Warehouse::query()->select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Batch $batch,
        BatchData $data,
        UpdateBatch $action,
    ): RedirectResponse {
        $action->handle($batch, $data);

        return to_route('batches.show', $batch)
            ->with('success', 'Batch updated.');
    }

    /**
     * @throws Throwable
     */
    public function destroy(Batch $batch, DeleteBatch $action): RedirectResponse
    {
        $action->handle($batch);

        return to_route('batches.index')
            ->with('success', 'Batch deleted.');
    }
}
