<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Data\Purchase\PurchaseItemData;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class AddPurchaseItem
{
    public function __construct(
        private ValidateStatusIsPending $validateStatus,
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, PurchaseItemData $data): PurchaseItem
    {
        return DB::transaction(function () use ($purchase, $data): PurchaseItem {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            $this->validateStatus->handle($purchase);

            $item = PurchaseItem::query()->forceCreate([
                'purchase_id' => $purchase->id,
                'product_id' => $data->product_id,
                'quantity' => $data->quantity,
                'received_quantity' => 0,
                'unit_cost' => $data->unit_cost,
                'subtotal' => $data->quantity * $data->unit_cost,
            ]);

            $this->recalculateTotal->handle($purchase);

            return $item->refresh();
        });
    }
}
