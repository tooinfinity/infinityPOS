<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Data\SaleReturn\CreateSaleReturnData;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class CreateSaleReturnAction
{
    /**
     * @throws Throwable
     */
    public function handle(CreateSaleReturnData $data): SaleReturn
    {
        $totalAmount = $this->calculateTotalAmount($data->items);

        $saleReturn = SaleReturn::query()->forceCreate([
            'sale_id' => $data->sale_id,
            'warehouse_id' => $data->warehouse_id,
            'user_id' => $data->user_id,
            'reference_no' => $this->generateReferenceNo(),
            'return_date' => $data->return_date,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'payment_status' => PaymentStatusEnum::Unpaid,
            'status' => ReturnStatusEnum::Pending,
            'note' => $data->note,
        ]);

        foreach ($data->items as $item) {
            SaleReturnItem::query()->forceCreate([
                'sale_return_id' => $saleReturn->id,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->quantity * $item->unit_price,
            ]);
        }

        return $saleReturn->refresh();
    }

    /**
     * @param  DataCollection<int, SaleReturnItemData>  $items
     */
    private function calculateTotalAmount(DataCollection $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item->quantity * $item->unit_price;
        }

        return $total;
    }

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'SRET-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (SaleReturn::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
