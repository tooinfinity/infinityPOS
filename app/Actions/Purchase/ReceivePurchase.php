<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\Batch\FindOrCreateBatch;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Actions\StockMovement\CreateStockMovement;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class ReceivePurchase
{
    public function __construct(
        private CreateStockMovement $createStockMovement,
        private ValidateStatusIsPending $validateStatus,
        private FindOrCreateBatch $findOrCreateBatch,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase): Purchase {
            /** @var Purchase $purchase */
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->with(['items.product', 'items.batch'])
                ->findOrFail($purchase->id);

            $this->validateStatus->validateTransition(
                $purchase->status,
                PurchaseStatusEnum::Received,
                'Purchase'
            );

            throw_if(
                $purchase->items()->count() === 0,
                InvalidArgumentException::class,
                'Cannot receive a purchase with no items.'
            );

            foreach ($purchase->items as $item) {
                $this->processItem($purchase, $item);
            }

            $purchase->forceFill(['status' => PurchaseStatusEnum::Received])->save();

            return $purchase->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function processItem(Purchase $purchase, PurchaseItem $item): void
    {
        $batch = $this->findOrCreateBatch->handle(
            $item->product_id,
            $purchase->warehouse_id,
            $item->unit_cost,
            $item->batch?->expires_at,
        );

        $previousQuantity = $batch->quantity;
        $newQuantity = $previousQuantity + $item->quantity;

        $batch->forceFill(['quantity' => $newQuantity])->save();

        $item->forceFill([
            'batch_id' => $batch->id,
            'received_quantity' => $item->quantity,
        ])->save();

        $this->createStockMovement->recordIn($batch, $item->quantity, $previousQuantity, $purchase, $purchase->user_id);
    }
}
