<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOperationException;
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
        ->toThrow(InsufficientStockException::class, 'Insufficient stock in batch '.$batch->id.'. Required: 10, Available: 5');
});

it('throws RuntimeException when item has no batch', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();

    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'batch_id' => null,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(InvalidOperationException::class, 'is missing a source batch');
});

it('uses existing destination batch when available', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $transfer->from_warehouse_id,
    ]);

    $existingDestBatch = Batch::factory()->create([
        'product_id' => $batch->product_id,
        'warehouse_id' => $transfer->to_warehouse_id,
        'cost_amount' => $batch->cost_amount,
        'quantity' => 20,
        'expires_at' => $batch->expires_at,
    ]);

    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 30,
    ]);

    $action = resolve(CompleteStockTransfer::class);
    $action->handle($transfer);

    expect($transfer->fresh()->status)->toBe(StockTransferStatusEnum::Completed);
    expect($batch->fresh()->quantity)->toBe(70);
    expect($existingDestBatch->fresh()->quantity)->toBe(50);
});

it('throws RuntimeException when source batch is null', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $product = App\Models\Product::factory()->create();

    Batch::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $transfer->to_warehouse_id,
        'cost_amount' => 0,
        'quantity' => 10,
        'expires_at' => null,
    ]);

    StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 15,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(InvalidOperationException::class, 'is missing a source batch');
});
