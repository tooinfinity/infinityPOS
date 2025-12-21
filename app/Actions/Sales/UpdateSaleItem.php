<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Data\Sales\UpdateSaleItemData;
use App\Models\SaleItem;

final readonly class UpdateSaleItem
{
    public function handle(SaleItem $saleItem, UpdateSaleItemData $data): SaleItem
    {
        $updateData = array_filter([
            'quantity' => $data->quantity,
            'price' => $data->price,
            'cost' => $data->cost,
            'discount' => $data->discount,
            'tax_amount' => $data->tax_amount,
            'total' => $data->total,
            'batch_number' => $data->batch_number,
            'expiry_date' => $data->expiry_date,
        ], fn (int|string|null $value): bool => $value !== null);

        $saleItem->update($updateData);

        return $saleItem;
    }
}
