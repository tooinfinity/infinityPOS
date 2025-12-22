<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use Inertia\Inertia;
use Inertia\Response;

final readonly class InventoryLevelController
{
    public function index(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->latest()
            ->paginate(20);

        return Inertia::render('inventory/levels/index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product, Store $store): Response
    {
        $layers = InventoryLayer::query()
            ->where('product_id', $product->id)
            ->where('store_id', $store->id)
            ->where('remaining_qty', '>', 0)
            ->oldest('received_at')
            ->get();

        $totalStock = $layers->sum('remaining_qty');

        return Inertia::render('inventory/levels/show', [
            'product' => $product,
            'store' => $store,
            'layers' => $layers,
            'total_stock' => $totalStock,
        ]);
    }
}
