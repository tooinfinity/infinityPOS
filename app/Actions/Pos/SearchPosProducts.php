<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

final readonly class SearchPosProducts
{
    private const int LIMIT = 20;

    /**
     * Search active, tracked products that have stock in the given warehouse.
     *
     * @return Collection<int, Product>
     */
    public function handle(string $query, int $warehouseId): Collection
    {
        return Product::query()
            ->with([
                'unit:id,short_name',
                // Relation is what with() expects — not HasMany
                'batches' => function (Relation $relation) use ($warehouseId): void {
                    $relation
                        ->where('warehouse_id', $warehouseId)
                        ->where('quantity', '>', 0)                        // inStock()
                        ->orderByRaw('expires_at IS NULL, expires_at ASC'); // fefo()
                },
            ])
            ->search($query)
            ->tracked()
            ->whereHas(
                'batches',
                function (Builder $query) use ($warehouseId): void {
                    $query
                        ->where('warehouse_id', $warehouseId)
                        ->where('quantity', '>', 0); // inStock()
                }
            )
            ->select([
                'id',
                'name',
                'sku',
                'barcode',
                'selling_price',
                'cost_price',
                'unit_id',
                'alert_quantity',
            ])
            ->withStockQuantity()
            ->limit(self::LIMIT)
            ->get();
    }
}
