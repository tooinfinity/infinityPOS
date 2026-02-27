<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateReturnAgainstOriginal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Data\PurchaseReturn\UpdatePurchaseReturnItemData;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdatePurchaseReturnItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private ValidateReturnAgainstOriginal $validateReturn,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturnItem $item, UpdatePurchaseReturnItemData $data): PurchaseReturnItem
    {
        return DB::transaction(function () use ($item, $data): PurchaseReturnItem {
            /** @var PurchaseReturnItem $item */
            $item = PurchaseReturnItem::query()
                ->lockForUpdate()
                ->with('purchaseReturn.purchase.items')
                ->findOrFail($item->id);

            $purchaseReturn = $item->purchaseReturn;

            $this->validateStatus->handle($purchaseReturn, 'Cannot update items in a non-pending purchase return.');

            $quantity = $data->quantity ?? $item->quantity;
            $unitCost = $data->unit_cost ?? $item->unit_cost;

            if ($data->quantity !== null) {
                $this->validateReturn->handle($item, $item->product_id, $item->batch_id, $quantity);
            }

            $item->forceFill([
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'subtotal' => $quantity * $unitCost,
            ])->save();

            $this->recalculateTotal->handle($purchaseReturn);

            return $item->refresh();
        });
    }
}
