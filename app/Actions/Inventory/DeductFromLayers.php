<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class DeductFromLayers
{
    /**
     * Deduct quantity from inventory layers using FIFO (First In, First Out).
     *
     * @throws Throwable
     */
    // TODO: Fix Me Later
    // @codeCoverageIgnoreStart
    public function handle(
        Product|int $product,
        Store|int $store,
        int $quantity,
        ?string $batchNumber = null
    ): void {
        throw_if($quantity <= 0, InvalidArgumentException::class, 'Quantity must be positive.');

        $productId = $product instanceof Product ? $product->id : $product;
        $storeId = $store instanceof Store ? $store->id : $store;

        DB::transaction(function () use ($productId, $storeId, $quantity, $batchNumber): void {
            $query = InventoryLayer::query()
                ->where('product_id', $productId)
                ->where('store_id', $storeId)
                ->where('remaining_qty', '>', 0)
                ->oldest('received_at'); // FIFO

            if ($batchNumber !== null) {
                $query->where('batch_number', $batchNumber);
            }

            $layers = $query->lockForUpdate()->get();

            $remainingToDeduct = $quantity;

            foreach ($layers as $layer) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $deductFromThisLayer = min($remainingToDeduct, $layer->remaining_qty);

                $layer->update([
                    'remaining_qty' => $layer->remaining_qty - $deductFromThisLayer,
                ]);

                $remainingToDeduct -= $deductFromThisLayer;
            }

            throw_if(
                $remainingToDeduct > 0,
                InvalidArgumentException::class,
                sprintf('Insufficient stock. Required: %d, Available: ', $quantity).($quantity - $remainingToDeduct)
            );
        });
    }

    // @codeCoverageIgnoreEnd
}
