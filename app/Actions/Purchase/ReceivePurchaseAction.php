<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\PurchaseStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final readonly class ReceivePurchaseAction
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase): Purchase {
            throw_if(
                ! in_array($purchase->status, [PurchaseStatusEnum::Pending, PurchaseStatusEnum::Ordered], true),
                RuntimeException::class,
                'Only pending or ordered purchases can be received.'
            );

            $purchase->load(['items.product', 'items.batch']);

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
        $batch = Batch::query()->forceCreate([
            'product_id' => $item->product_id,
            'warehouse_id' => $purchase->warehouse_id,
            'batch_number' => $this->generateBatchNumber($item),
            'cost_amount' => $item->unit_cost,
            'quantity' => $item->quantity,
            'expires_at' => $item->batch?->expires_at,
        ])->refresh();

        $item->forceFill([
            'batch_id' => $batch->id,
            'received_quantity' => $item->quantity,
        ])->save();

        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $purchase->warehouse_id,
            product_id: $item->product_id,
            type: StockMovementTypeEnum::In,
            quantity: $item->quantity,
            previous_quantity: 0,
            current_quantity: $item->quantity,
            reference_type: Purchase::class,
            reference_id: $purchase->id,
            batch_id: $batch->id,
            user_id: $purchase->user_id,
            note: 'Purchase receipt',
            created_at: null,
        ));
    }

    private function generateBatchNumber(PurchaseItem $item): string
    {
        return 'BAT-'.now()->format('YmdHis').'-'.$item->product_id.'-'.mb_strtoupper(Str::random(6));
    }
}
