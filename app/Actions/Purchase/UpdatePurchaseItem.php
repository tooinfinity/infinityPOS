<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Data\Purchase\UpdatePurchaseItemData;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdatePurchaseItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PurchaseItem $item, UpdatePurchaseItemData $data): PurchaseItem
    {
        return DB::transaction(function () use ($item, $data): PurchaseItem {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($item->purchase_id);

            $this->validateStatus->handle($purchase);

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
                $this->recalculateTotal->handle($purchase);
            }

            return $item->refresh();
        });
    }
}
