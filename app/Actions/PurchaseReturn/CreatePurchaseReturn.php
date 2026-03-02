<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\RecalculateParentTotal;
use App\Data\PurchaseReturn\CreatePurchaseReturnData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePurchaseReturn
{
    public function __construct(
        private GenerateReferenceNo $generateReferenceNo,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreatePurchaseReturnData $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data): PurchaseReturn {
            $purchaseReturn = PurchaseReturn::query()->forceCreate([
                'purchase_id' => $data->purchase_id,
                'warehouse_id' => $data->warehouse_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('PUR-RETURN', PurchaseReturn::class),
                'return_date' => $data->return_date,
                'total_amount' => 0,
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

            $this->recalculateTotal->handle($purchaseReturn);

            return $purchaseReturn->refresh();
        });
    }
}
