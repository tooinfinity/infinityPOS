<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Data\Purchase\PurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class AddPurchaseItemAction
{
    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, PurchaseItemData $data): PurchaseItem
    {
        return DB::transaction(function () use ($purchase, $data): PurchaseItem {
            throw_if(
                $purchase->status !== PurchaseStatusEnum::Pending,
                RuntimeException::class,
                'Items can only be added to pending purchases.'
            );

            $item = PurchaseItem::query()->forceCreate([
                'purchase_id' => $purchase->id,
                'product_id' => $data->product_id,
                'quantity' => $data->quantity,
                'received_quantity' => 0,
                'unit_cost' => $data->unit_cost,
                'subtotal' => $data->quantity * $data->unit_cost,
            ]);

            $this->recalculatePurchaseTotal($purchase);

            return $item->refresh();
        });
    }

    private function recalculatePurchaseTotal(Purchase $purchase): void
    {
        $total = PurchaseItem::query()
            ->where('purchase_id', $purchase->id)
            ->sum('subtotal');

        $purchase->forceFill(['total_amount' => $total])->save();
    }
}
