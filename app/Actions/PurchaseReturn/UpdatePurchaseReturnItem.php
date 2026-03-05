<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateReturnAgainstOriginal;
use App\Data\PurchaseReturn\UpdatePurchaseReturnItemData;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdatePurchaseReturnItem
{
    public function __construct(
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

            $quantity = $data->quantity instanceof Optional ? $item->quantity : $data->quantity;
            $unitCost = $data->unit_cost instanceof Optional ? $item->unit_cost : $data->unit_cost;

            if (! $data->quantity instanceof Optional) {
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
