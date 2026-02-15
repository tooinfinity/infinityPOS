<?php

declare(strict_types=1);

use App\Actions\StockTransfer\UpdateStockTransferItem;
use App\Enums\StockTransferStatusEnum;
use App\Models\Batch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

it('may update item quantity', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'quantity' => 10,
    ]);

    $action = resolve(UpdateStockTransferItem::class);

    $action->handle($item, [
        'quantity' => 25,
    ]);

    expect($item->fresh()->quantity)->toBe(25);
});

it('may update item batch', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create();
    $newBatch = Batch::factory()->create();

    $action = resolve(UpdateStockTransferItem::class);

    $action->handle($item, [
        'batch_id' => $newBatch->id,
    ]);

    expect($item->fresh()->batch_id)->toBe($newBatch->id);
});

it('throws exception when updating item in non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'quantity' => 10,
    ]);

    $action = resolve(UpdateStockTransferItem::class);

    expect(fn () => $action->handle($item, [
        'quantity' => 20,
    ]))->toThrow(RuntimeException::class, 'Items can only be updated when transfer is pending.');
});

it('throws exception when updating item in cancelled transfer', function (): void {
    $transfer = StockTransfer::factory()->create([
        'status' => StockTransferStatusEnum::Cancelled,
    ]);
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create();

    $action = resolve(UpdateStockTransferItem::class);

    expect(fn () => $action->handle($item, [
        'quantity' => 20,
    ]))->toThrow(RuntimeException::class, 'Items can only be updated when transfer is pending.');
});
