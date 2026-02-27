<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateReturnAgainstOriginal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class AddPurchaseReturnItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private ValidateReturnAgainstOriginal $validateReturn,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, PurchaseReturnItemData $data): PurchaseReturnItem
    {
        return DB::transaction(function () use ($purchaseReturn, $data): PurchaseReturnItem {
            /** @var PurchaseReturn $purchaseReturn */
            $purchaseReturn = PurchaseReturn::query()
                ->lockForUpdate()
                ->with('purchase.items')
                ->findOrFail($purchaseReturn->id);

            $this->validateStatus->handle($purchaseReturn);
            $this->validateReturn->validateNewReturnForPurchase($purchaseReturn, $data->product_id, $data->batch_id, $data->quantity);

            $item = PurchaseReturnItem::query()->forceCreate([
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $data->product_id,
                'batch_id' => $data->batch_id,
                'quantity' => $data->quantity,
                'unit_cost' => $data->unit_cost,
                'subtotal' => $data->quantity * $data->unit_cost,
            ]);

            $this->recalculateTotal->handle($purchaseReturn);

            return $item;
        });
    }
}
