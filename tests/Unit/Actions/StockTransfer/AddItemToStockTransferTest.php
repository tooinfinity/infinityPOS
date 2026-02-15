<?php

declare(strict_types=1);

use App\Actions\StockTransfer\AddItemToStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

it('may add item to pending transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $product = Product::factory()->create();

    $action = resolve(AddItemToStockTransfer::class);

    $item = $action->handle($transfer, [
        'product_id' => $product->id,
        'quantity' => 15,
    ]);

    expect($item)->toBeInstanceOf(StockTransferItem::class)
        ->and($item->stock_transfer_id)->toBe($transfer->id)
        ->and($item->product_id)->toBe($product->id)
        ->and($item->quantity)->toBe(15);
});

it('may add item with batch to pending transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->create();

    $action = resolve(AddItemToStockTransfer::class);

    $item = $action->handle($transfer, [
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 20,
    ]);

    expect($item->batch_id)->toBe($batch->id);
});

it('throws exception when adding to non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create();
    $product = Product::factory()->create();

    $action = resolve(AddItemToStockTransfer::class);

    expect(fn () => $action->handle($transfer, [
        'product_id' => $product->id,
        'quantity' => 10,
    ]))->toThrow(RuntimeException::class, 'Items can only be added to pending transfers.');
});

it('throws exception when adding to cancelled transfer', function (): void {
    $transfer = StockTransfer::factory()->create([
        'status' => StockTransferStatusEnum::Cancelled,
    ]);
    $product = Product::factory()->create();

    $action = resolve(AddItemToStockTransfer::class);

    expect(fn () => $action->handle($transfer, [
        'product_id' => $product->id,
        'quantity' => 10,
    ]))->toThrow(RuntimeException::class, 'Items can only be added to pending transfers.');
});
