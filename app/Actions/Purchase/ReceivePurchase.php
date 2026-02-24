<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\PurchaseStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class ReceivePurchase
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

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

            throw_if(
                ! $purchase->status->canTransitionTo(PurchaseStatusEnum::Received),
                StateTransitionException::class,
                $purchase->status->label(),
                PurchaseStatusEnum::Received->label()
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
        $batch = $this->findOrCreateBatch($purchase, $item);

        $previousQuantity = $batch->quantity;
        $newQuantity = $previousQuantity + $item->quantity;

        $batch->forceFill(['quantity' => $newQuantity])->save();

        $item->forceFill([
            'batch_id' => $batch->id,
            'received_quantity' => $item->quantity,
        ])->save();

        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $purchase->warehouse_id,
            product_id: $item->product_id,
            type: StockMovementTypeEnum::In,
            quantity: $item->quantity,
            previous_quantity: $previousQuantity,
            current_quantity: $newQuantity,
            reference_type: Purchase::class,
            reference_id: $purchase->id,
            batch_id: $batch->id,
            user_id: $purchase->user_id,
            note: 'Purchase receipt',
            created_at: null,
        ));
    }

    private function findOrCreateBatch(Purchase $purchase, PurchaseItem $item): Batch
    {
        $expiresAt = $item->batch?->expires_at;

        $existingBatch = Batch::query()
            ->lockForUpdate()
            ->where('product_id', $item->product_id)
            ->where('warehouse_id', $purchase->warehouse_id)
            ->where('cost_amount', $item->unit_cost)
            ->where(function (Builder $query) use ($expiresAt): void {
                $query->where('expires_at', $expiresAt)
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('expires_at')
                            ->where(function (Builder $query): void {
                                $query->whereNull('expires_at');
                            });
                    });
            })
            ->first();

        return $existingBatch ?? Batch::query()->forceCreate([
            'product_id' => $item->product_id,
            'warehouse_id' => $purchase->warehouse_id,
            'batch_number' => $this->generateBatchNumber($item),
            'cost_amount' => $item->unit_cost,
            'quantity' => 0,
            'expires_at' => $item->batch?->expires_at,
        ]);
    }

    private function generateBatchNumber(PurchaseItem $item): string
    {
        return 'BAT-'.now()->format('YmdHis').'-'.$item->product_id.'-'.mb_strtoupper(Str::random(6));
    }
}
