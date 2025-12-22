<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Data\Inventory\CreateInventoryLayerData;
use App\Models\InventoryLayer;

final readonly class CreateInventoryLayer
{
    public function handle(CreateInventoryLayerData $data): InventoryLayer
    {
        return InventoryLayer::query()->create([
            'product_id' => $data->product_id,
            'store_id' => $data->store_id,
            'batch_number' => $data->batch_number,
            'expiry_date' => $data->expiry_date,
            'unit_cost' => $data->unit_cost,
            'received_qty' => $data->received_qty,
            'remaining_qty' => $data->remaining_qty,
            'received_at' => $data->received_at,
        ]);
    }
}
