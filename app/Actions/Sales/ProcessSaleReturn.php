<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Data\Sales\ProcessSaleReturnData;
use App\Enums\SaleReturnStatusEnum;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessSaleReturn
{
    /**
     * @throws Throwable
     */
    public function handle(ProcessSaleReturnData $data): SaleReturn
    {
        return DB::transaction(function () use ($data) {
            $saleReturn = SaleReturn::query()->create([
                'reference' => $data->reference,
                'sale_id' => $data->sale_id,
                'client_id' => $data->client_id,
                'store_id' => $data->store_id,
                'subtotal' => $data->subtotal,
                'discount' => $data->discount,
                'tax' => $data->tax,
                'total' => $data->total,
                'refunded' => 0,
                'status' => SaleReturnStatusEnum::PENDING,
                'reason' => $data->reason,
                'notes' => $data->notes,
                'created_by' => $data->created_by,
            ]);

            foreach ($data->items as $itemData) {
                $saleReturn->items()->create([
                    'product_id' => $itemData->product_id,
                    'sale_item_id' => $itemData->sale_item_id,
                    'quantity' => $itemData->quantity,
                    'price' => $itemData->price,
                    'cost' => $itemData->cost,
                    'discount' => $itemData->discount,
                    'tax_amount' => $itemData->tax_amount,
                    'total' => $itemData->total,
                ]);
            }

            return $saleReturn;
        });
    }
}
