<?php

declare(strict_types=1);

namespace App\Actions\StockMovement;

use App\Data\StockMovement\RecordStockMovementData;
use App\Models\StockMovement;

final readonly class RecordStockMovement
{
    public function handle(RecordStockMovementData $data): StockMovement
    {
        return StockMovement::query()->forceCreate([
            'warehouse_id' => $data->warehouse_id,
            'product_id' => $data->product_id,
            'type' => $data->type,
            'quantity' => $data->quantity,
            'previous_quantity' => $data->previous_quantity,
            'current_quantity' => $data->current_quantity,
            'reference_type' => $data->reference_type,
            'reference_id' => $data->reference_id,
            'batch_id' => $data->batch_id,
            'user_id' => $data->user_id,
            'note' => $data->note,
        ])->refresh();
    }
}
