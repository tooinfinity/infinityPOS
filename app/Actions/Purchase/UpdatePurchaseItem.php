<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Data\Purchase\UpdatePurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdatePurchaseItem
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseItem $item, UpdatePurchaseItemData $data): PurchaseItem
    {
        return DB::transaction(function () use ($item, $data): PurchaseItem {
            throw_if(
                $item->purchase->status !== PurchaseStatusEnum::Pending,
                RuntimeException::class,
                'Items can only be updated on pending purchases.'
            );

            $updateData = [];

            if (! $data->quantity instanceof Optional) {
                $updateData['quantity'] = $data->quantity;
            }

            if (! $data->unit_cost instanceof Optional) {
                $updateData['unit_cost'] = $data->unit_cost;
            }

            if (count($updateData) > 0) {
                $quantity = $updateData['quantity'] ?? $item->quantity;
                $unitCost = $updateData['unit_cost'] ?? $item->unit_cost;
                $updateData['subtotal'] = $quantity * $unitCost;

                $item->update($updateData);
                $this->recalculatePurchaseTotal($item);
            }

            return $item->refresh();
        });
    }

    private function recalculatePurchaseTotal(PurchaseItem $item): void
    {
        $purchase = $item->purchase;

        $total = PurchaseItem::query()
            ->where('purchase_id', $purchase->id)
            ->lockForUpdate()
            ->sum('subtotal');

        $purchase->forceFill(['total_amount' => $total])->save();
    }
}
