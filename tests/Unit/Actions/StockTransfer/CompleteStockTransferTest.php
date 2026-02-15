<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

it('may complete pending transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create();
    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    $action->handle($transfer);

    expect($transfer->fresh()->status)->toBe(StockTransferStatusEnum::Completed);
});

it('decreases source batch quantity', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $transfer->from_warehouse_id,
    ]);
    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 25,
    ]);

    $action = resolve(CompleteStockTransfer::class);
    $action->handle($transfer);

    expect($batch->fresh()->quantity)->toBe(75);
});

it('creates destination batch', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $transfer->from_warehouse_id,
    ]);
    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 30,
    ]);

    $action = resolve(CompleteStockTransfer::class);
    $action->handle($transfer);

    $destinationBatch = Batch::query()
        ->where('warehouse_id', $transfer->to_warehouse_id)
        ->where('product_id', $batch->product_id)
        ->first();

    expect($destinationBatch)->not->toBeNull()
        ->and($destinationBatch->quantity)->toBe(30);
});

it('records stock movements', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $transfer->from_warehouse_id,
    ]);
    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 20,
    ]);

    $action = resolve(CompleteStockTransfer::class);
    $action->handle($transfer);

    $movements = StockMovement::query()
        ->where('reference_type', StockTransfer::class)
        ->where('reference_id', $transfer->id)
        ->get();

    expect($movements)->toHaveCount(2);
});

it('throws exception when source has insufficient stock', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(5)->create([
        'warehouse_id' => $transfer->from_warehouse_id,
    ]);
    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(RuntimeException::class, 'Insufficient stock in batch');
});

it('throws exception when completing non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create();

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(RuntimeException::class, 'Only pending transfers can be completed.');
});

it('throws exception when completing already completed transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create();

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(RuntimeException::class, 'Only pending transfers can be completed.');
});

it('throws exception when completing cancelled transfer', function (): void {
    $transfer = StockTransfer::factory()->create([
        'status' => StockTransferStatusEnum::Cancelled,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(RuntimeException::class, 'Only pending transfers can be completed.');
});
