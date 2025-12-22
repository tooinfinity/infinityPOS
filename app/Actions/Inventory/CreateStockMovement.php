<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Models\StockMovement;

final readonly class CreateStockMovement
{
    /**
     * Create a stock movement record.
     */
    public function handle(
        int $productId,
        int $storeId,
        int $quantity,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $batchNumber = null,
        ?string $notes = null,
        ?int $createdBy = null
    ): StockMovement {
        return StockMovement::query()->create([
            'product_id' => $productId,
            'store_id' => $storeId,
            'quantity' => $quantity,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'batch_number' => $batchNumber,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);
    }
}
