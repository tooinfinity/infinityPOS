<?php

declare(strict_types=1);

use App\Actions\StockTransfer\RemoveItemFromStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

it('may remove item from pending transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create();

    $action = resolve(RemoveItemFromStockTransfer::class);

    $result = $action->handle($transfer, $item);

    expect($result)->toBeTrue()
        ->and(StockTransferItem::query()->find($item->id))->toBeNull();
});

it('throws exception when removing from non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create();

    $action = resolve(RemoveItemFromStockTransfer::class);

    expect(fn () => $action->handle($transfer, $item))
        ->toThrow(RuntimeException::class, 'Items can only be removed from pending transfers.');
});

it('throws exception when removing from cancelled transfer', function (): void {
    $transfer = StockTransfer::factory()->create([
        'status' => StockTransferStatusEnum::Cancelled,
    ]);
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create();

    $action = resolve(RemoveItemFromStockTransfer::class);

    expect(fn () => $action->handle($transfer, $item))
        ->toThrow(RuntimeException::class, 'Items can only be removed from pending transfers.');
});

it('throws exception when item does not belong to transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $otherTransfer = StockTransfer::factory()->pending()->create();
    $item = StockTransferItem::factory()->forStockTransfer($otherTransfer)->create();

    $action = resolve(RemoveItemFromStockTransfer::class);

    expect(fn () => $action->handle($transfer, $item))
        ->toThrow(RuntimeException::class, 'Item does not belong to this transfer.');
});
