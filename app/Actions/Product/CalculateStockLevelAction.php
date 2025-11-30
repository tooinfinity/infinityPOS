<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Throwable;

final readonly class CalculateStockLevelAction
{
    /**
     * Execute the action.
     *
     * @throws Throwable
     */
    public function handle(Product $product): array
    {
        $totalStock = (float) $product->stores()->sum('quantity');
        $alertQuantity = $product->alert_quantity;

        return [
            'total_stock' => $totalStock,
            'alert_quantity' => $alertQuantity,
            'is_low_stock' => $totalStock <= $alertQuantity,
            'percentage' => $alertQuantity > 0
                ? round(($totalStock / $alertQuantity) * 100, 2)
                : 0,
        ];
    }
}
