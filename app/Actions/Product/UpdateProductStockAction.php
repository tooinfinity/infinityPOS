<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () use ($product, $store, $quantity): void {
            $product->stores()->syncWithoutDetaching([
                $store->id => [
                    'quantity' => DB::raw('quantity + '.$quantity),
                    'updated_at' => now(),
                ],
            ]);
        });
    }
}
