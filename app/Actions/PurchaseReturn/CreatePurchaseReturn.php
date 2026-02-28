<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\GenerateReferenceNo;
use App\Data\PurchaseReturn\CreatePurchaseReturnData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePurchaseReturn
{
    /**
     * @throws Throwable
     */
    public function handle(CreatePurchaseReturnData $data): PurchaseReturn
    {
        return DB::transaction(static function () use ($data): PurchaseReturn {
            $totalAmount = $data->items->toCollection()->reduce(fn (int $total, PurchaseReturnItemData $item): int => $total + ($item->quantity * $item->unit_cost), 0);

            $purchaseReturn = PurchaseReturn::query()->forceCreate([
                'purchase_id' => $data->purchase_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => new GenerateReferenceNo('PUR-RETURN', PurchaseReturn::query())->handle(),
                'return_date' => $data->return_date,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'status' => ReturnStatusEnum::Pending,
                'note' => $data->note,
            ]);

            foreach ($data->items as $item) {
                PurchaseReturnItem::query()->forceCreate([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'subtotal' => $item->quantity * $item->unit_cost,
                ]);
            }

            return $purchaseReturn->refresh();
        });
    }
}
