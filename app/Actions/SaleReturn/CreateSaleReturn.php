<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\CalculateTotalFromItems;
use App\Data\SaleReturn\CreateSaleReturnData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateSaleReturn
{
    public function __construct(private CalculateTotalFromItems $calculateTotal) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateSaleReturnData $data): SaleReturn
    {
        return DB::transaction(function () use ($data): SaleReturn {
            $totalAmount = $this->calculateTotal->handle($data->items, 'unit_price');

            $saleReturn = SaleReturn::query()->forceCreate([
                'sale_id' => $data->sale_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => new GenerateReferenceNo('SAL-RETURN', SaleReturn::query())->handle(),
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
        });
    }
}
