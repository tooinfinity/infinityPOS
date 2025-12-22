<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Data\Inventory\UpdateInventoryLayerData;
use App\Models\InventoryLayer;

final readonly class UpdateInventoryLayer
{
    public function handle(InventoryLayer $layer, UpdateInventoryLayerData $data): InventoryLayer
    {
        if ($data->remaining_qty !== null) {
            $layer->update([
                'remaining_qty' => $data->remaining_qty,
            ]);
        }

        return $layer;
    }
}
