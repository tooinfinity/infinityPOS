<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\Inventory\BulkStockAdjustment;
use App\Data\Inventory\BulkStockAdjustmentData;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class BulkStockAdjustmentController
{
    public function create(): Response
    {
        $products = Product::query()->where('is_active', true)->latest()->get();
        $stores = Store::query()->latest()->get();

        return Inertia::render('inventory/bulk-adjustment/create', [
            'products' => $products,
            'stores' => $stores,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(BulkStockAdjustmentData $data, BulkStockAdjustment $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('inventory.adjustments.index');
    }
}
