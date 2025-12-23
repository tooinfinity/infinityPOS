<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Models\Sale;
use App\Settings\InventorySettings;
use InvalidArgumentException;

final readonly class ValidateCartStock
{
    public function __construct(private InventorySettings $inventorySettings) {}

    /**
     * Validate all items in a sale have sufficient stock at the given store.
     *
     * @throws InvalidArgumentException
     */
    public function handle(Sale $sale, int $storeId): void
    {
        if (! $this->inventorySettings->auto_deduct_stock) {
            return;
        }

        foreach ($sale->items as $item) {
            $product = $item->product;
            $availableStock = $product->getAvailableStock($storeId);

            throw_if(
                $item->quantity > $availableStock,
                InvalidArgumentException::class,
                sprintf(
                    'Insufficient stock for product "%s". Available: %d, Required: %d',
                    $product->name,
                    $availableStock,
                    (int) $item->quantity
                )
            );
        }
    }
}
