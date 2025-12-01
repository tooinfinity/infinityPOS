<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class UpdateProductStockAction
{
    /**
     * Execute the action.
     *
     * @throws Throwable
     */
    public function handle(Product $product, Store $store, float $quantity): void
    {
        throw_if($quantity < 0, InvalidArgumentException::class, 'Quantity cannot be negative');

        DB::transaction(function () use ($product, $store, $quantity): void {
            $product->stores()->syncWithoutDetaching([
                $store->id => ['quantity' => 0],
            ]);

            DB::table('store_stock')
                ->where('product_id', $product->id)
                ->where('store_id', $store->id)
                ->increment('quantity', $quantity, ['updated_at' => now()]);
        });
    }
}
