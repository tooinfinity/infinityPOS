<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\Warehouse;

it('may complete a stock transfer', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();

    $batch = Batch::factory()->for($product)->for($fromWarehouse)->create([
        'quantity' => 100,
    ]);

    $transfer = StockTransfer::factory()->for($fromWarehouse, 'fromWarehouse')->for($toWarehouse, 'toWarehouse')->create([
        'status' => StockTransferStatusEnum::Pending,
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    $completedTransfer = $action->handle($transfer);

    expect($completedTransfer->status)->toBe(StockTransferStatusEnum::Completed);
});

it('throws exception when transfer cannot transition to completed', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();

    $batch = Batch::factory()->for($product)->for($fromWarehouse)->create([
        'quantity' => 100,
    ]);

    $transfer = StockTransfer::factory()->for($fromWarehouse, 'fromWarehouse')->for($toWarehouse, 'toWarehouse')->create([
        'status' => StockTransferStatusEnum::Completed,
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))->toThrow(App\Exceptions\StateTransitionException::class);
});

it('throws exception when batch does not belong to source warehouse', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();

    $batch = Batch::factory()->for($product)->for($otherWarehouse)->create([
        'quantity' => 100,
    ]);

    $transfer = StockTransfer::factory()->for($fromWarehouse, 'fromWarehouse')->for($toWarehouse, 'toWarehouse')->create([
        'status' => StockTransferStatusEnum::Pending,
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    expect(fn () => $action->handle($transfer))->toThrow(App\Exceptions\InvalidBatchException::class);
});
