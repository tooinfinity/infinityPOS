<?php

declare(strict_types=1);

namespace App\Jobs\Inventory;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class RebuildInventoryLayersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ?int $storeId = null,
        public readonly ?int $productId = null,
    ) {}

    public function handle(): void
    {
        // This job rebuilds inventory layers from historical stock movements and purchases.
        // It's useful for data migration, audit corrections, or recovering from layer corruption.

        $query = \App\Models\StockMovement::query()
            ->with(['product', 'store', 'source'])->oldest()
            ->orderBy('id');

        if ($this->storeId !== null) {
            $query->where('store_id', $this->storeId);
        }

        if ($this->productId !== null) {
            $query->where('product_id', $this->productId);
        }

        // Clear existing layers in scope
        $layerQuery = \App\Models\InventoryLayer::query();
        if ($this->storeId !== null) {
            $layerQuery->where('store_id', $this->storeId);
        }

        if ($this->productId !== null) {
            $layerQuery->where('product_id', $this->productId);
        }

        $layerQuery->delete();

        // Process all stock movements in chronological order
        $query->chunk(500, function (Collection $movements): void {
            foreach ($movements as $movement) {
                $this->processMovement($movement);
            }
        });

        \Illuminate\Support\Facades\Log::info('Inventory layers rebuilt', [
            'store_id' => $this->storeId,
            'product_id' => $this->productId,
        ]);
    }

    private function processMovement(\App\Models\StockMovement $movement): void
    {
        // Only create layers for incoming movements (positive quantity)
        if ($movement->quantity <= 0) {
            return;
        }

        // Determine unit cost from the source (Purchase or PurchaseItem)
        $unitCost = $this->determineUnitCost($movement);

        // Create a new inventory layer
        \App\Models\InventoryLayer::query()->create([
            'product_id' => $movement->product_id,
            'store_id' => $movement->store_id,
            'batch_number' => $movement->batch_number,
            'expiry_date' => null, // expiry_date is not tracked in stock_movements
            'unit_cost' => $unitCost,
            'received_qty' => $movement->quantity,
            'remaining_qty' => $movement->quantity, // Initially all available
            'received_at' => $movement->created_at,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function determineUnitCost(\App\Models\StockMovement $movement): int
    {
        // Try to get cost from the source model
        $source = $movement->source;

        if ($source instanceof \App\Models\Purchase) {
            // Find the matching purchase item
            $purchaseItem = $source->items()
                ->where('product_id', $movement->product_id)
                ->first();

            if ($purchaseItem !== null) {
                return (int) $purchaseItem->cost;
            }
        }

        if ($source instanceof \App\Models\PurchaseItem) {
            return (int) $source->cost;
        }

        // Fallback: use product's current cost
        $product = $movement->product;
        if ($product !== null && $product->cost > 0) {
            return (int) $product->cost;
        }

        // Last resort: default to 0
        return 0;
    }
}
