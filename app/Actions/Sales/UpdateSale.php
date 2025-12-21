<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Data\Sales\UpdateSaleData;
use App\Models\Sale;

final readonly class UpdateSale
{
    public function handle(Sale $sale, UpdateSaleData $data): Sale
    {
        $updateData = array_filter([
            'reference' => $data->reference,
            'client_id' => $data->client_id,
            'store_id' => $data->store_id,
            'subtotal' => $data->subtotal,
            'discount' => $data->discount,
            'tax' => $data->tax,
            'total' => $data->total,
            'notes' => $data->notes,
            'updated_by' => $data->updated_by,
        ], fn (string|int|null $value): bool => $value !== null);

        $sale->update($updateData);

        return $sale;
    }
}
