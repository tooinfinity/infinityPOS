<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Data\Inventory\CreateInventoryLayerData;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class TransferStock
{
    public function __construct(
        private DeductFromLayers $deductFromLayers,
        private CreateInventoryLayer $createInventoryLayer,
    ) {}

    /**
     * Transfer stock directly between stores (without creating a transfer document).
     *
     * @return array{from: StockMovement, to: StockMovement}
     *
     * @throws Throwable
     */
    public function handle(
        Product|int $product,
        Store|int $fromStore,
        Store|int $toStore,
        int $quantity,
        ?string $batchNumber = null,
        ?string $notes = null,
        ?int $userId = null
    ): array {
        throw_if($quantity <= 0, InvalidArgumentException::class, 'Transfer quantity must be positive.');

        $productId = $product instanceof Product ? $product->id : $product;
        $fromStoreId = $fromStore instanceof Store ? $fromStore->id : $fromStore;
        $toStoreId = $toStore instanceof Store ? $toStore->id : $toStore;

        throw_if($fromStoreId === $toStoreId, InvalidArgumentException::class, 'Cannot transfer to the same store.');

        return DB::transaction(function () use ($productId, $fromStoreId, $toStoreId, $quantity, $batchNumber, $notes, $userId): array {
            // Deduct from source store
            $this->deductFromLayers->handle(
                product: $productId,
                store: $fromStoreId,
                quantity: $quantity,
                batchNumber: $batchNumber
            );

            // Create outgoing movement
            $outgoingMovement = StockMovement::query()->create([
                'product_id' => $productId,
                'store_id' => $fromStoreId,
                'quantity' => -$quantity,
                'source_type' => null,
                'source_id' => null,
                'batch_number' => $batchNumber,
                'notes' => 'Direct transfer out'.($notes ? ': '.$notes : ''),
                'created_by' => $userId,
            ]);

            // Add to destination store
            $this->createInventoryLayer->handle(
                new CreateInventoryLayerData(
                    product_id: $productId,
                    store_id: $toStoreId,
                    batch_number: $batchNumber,
                    expiry_date: null,
                    unit_cost: 0,
                    received_qty: $quantity,
                    remaining_qty: $quantity,
                    received_at: now()->toDateTimeString(),
                )
            );

            // Create incoming movement
            $incomingMovement = StockMovement::query()->create([
                'product_id' => $productId,
                'store_id' => $toStoreId,
                'quantity' => $quantity,
                'source_type' => null,
                'source_id' => null,
                'batch_number' => $batchNumber,
                'notes' => 'Direct transfer in'.($notes ? ': '.$notes : ''),
                'created_by' => $userId,
            ]);

            return [
                'from' => $outgoingMovement,
                'to' => $incomingMovement,
            ];
        });
    }
}
