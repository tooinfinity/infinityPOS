<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\Batch\FindOrCreateBatch;
use App\Actions\Stock\AddStock;
use App\Data\Purchase\ReceivePurchaseData;
use App\Data\Purchase\ReceivePurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Exceptions\ItemNotFoundException;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ReceivePurchase
{
    public function __construct(
        private FindOrCreateBatch $findOrCreateBatch,
        private AddStock $addStock,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, ReceivePurchaseData $data): Purchase
    {
        /** @var Purchase $result */
        $result = DB::transaction(function () use ($purchase, $data): Purchase {
            if (! $purchase->status->canTransitionTo(PurchaseStatusEnum::Received)) {
                throw new StateTransitionException($purchase->status->value, PurchaseStatusEnum::Received->value);
            }

            $purchase->load('items');

            $data->items->toCollection()
                ->each(function (ReceivePurchaseItemData $receivedItem) use ($purchase): void {
                    /** @var PurchaseItem|null $purchaseItem */
                    $purchaseItem = $purchase->items
                        ->firstWhere('id', $receivedItem->purchase_item_id);

                    if (! $purchaseItem instanceof PurchaseItem) {
                        throw new ItemNotFoundException(
                            'PurchaseItem',
                            "Purchase #{$purchase->id}",
                            "Item #{$receivedItem->purchase_item_id} does not belong to this purchase."
                        );
                    }

                    if ($receivedItem->received_quantity === 0) {
                        return; // skip — nothing received for this item
                    }

                    $expiresAt = $receivedItem->expires_at
                        ?? ($purchaseItem->expires_at ? Date::parse($purchaseItem->expires_at) : null);

                    $batch = $this->findOrCreateBatch->handle(
                        productId: $purchaseItem->product_id,
                        warehouseId: $purchase->warehouse_id,
                        costAmount: $purchaseItem->unit_cost,
                        expiresAt: $expiresAt,
                    );

                    $this->addStock->handle(
                        batch: $batch,
                        quantity: $receivedItem->received_quantity,
                        reference: $purchase,
                        note: "Purchase received: {$purchase->reference_no}",
                    );

                    $purchaseItem->forceFill([
                        'received_quantity' => $receivedItem->received_quantity,
                        'batch_id' => $batch->id,
                    ])->save();
                });

            $allFullyReceived = $purchase->items->every(fn ($item): bool => $item->received_quantity === $item->quantity);

            $purchase->forceFill([
                'status' => $allFullyReceived ? PurchaseStatusEnum::Received : PurchaseStatusEnum::Pending,
            ])->save();

            return $purchase->load([
                'items.product',
                'items.batch',
                'supplier',
                'warehouse',
            ]);
        });

        return $result;
    }
}
