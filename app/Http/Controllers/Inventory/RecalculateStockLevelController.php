<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Actions\Inventory\RecalculateStockLevels;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;

final readonly class RecalculateStockLevelController
{
    public function __invoke(Product $product, Store $store, RecalculateStockLevels $action): RedirectResponse
    {
        $total = $action->handle($product, $store);

        return back()->with('message', sprintf('Stock recalculated: %d units', $total));
    }
}
