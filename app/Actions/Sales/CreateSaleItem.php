<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Data\Sales\CreateSaleItemData;
use App\Models\Sale;
use App\Models\SaleItem;

final readonly class CreateSaleItem
{
    public function handle(Sale $sale, CreateSaleItemData $data): SaleItem
    {
        return $sale->items()->create([
            'product_id' => $data->product_id,
            'quantity' => $data->quantity,
            'price' => $data->price,
            'cost' => $data->cost,
            'discount' => $data->discount,
            'tax_amount' => $data->tax_amount,
            'total' => $data->total,
            'batch_number' => $data->batch_number,
            'expiry_date' => $data->expiry_date,
        ]);
    }
}
