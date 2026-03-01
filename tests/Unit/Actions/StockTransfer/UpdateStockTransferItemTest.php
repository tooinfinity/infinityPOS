<?php

declare(strict_types=1);

use App\Actions\StockTransfer\UpdateStockTransferItem;
use App\Data\StockTransfer\UpdateStockTransferItemData;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Spatie\LaravelData\Optional;

it('may update item quantity', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'quantity' => 10,
    ]);

    $action = resolve(UpdateStockTransferItem::class);

    $data = new UpdateStockTransferItemData(
        batch_id: Optional::create(),
        quantity: 25,
    );

    $action->handle($item, $data);

    expect($item->fresh()->quantity)->toBe(25);
});

it('may update item batch', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create();
    $newBatch = Batch::factory()->create();

    $action = resolve(UpdateStockTransferItem::class);

    $data = new UpdateStockTransferItemData(
        batch_id: $newBatch->id,
        quantity: Optional::create(),
    );

    $action->handle($item, $data);

    expect($item->fresh()->batch_id)->toBe($newBatch->id);
});

it('throws exception when updating item in non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create();
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create([
        'quantity' => 10,
    ]);

    $action = resolve(UpdateStockTransferItem::class);

    $data = new UpdateStockTransferItemData(
        batch_id: Optional::create(),
        quantity: 20,
    );

    expect(fn () => $action->handle($item, $data))->toThrow(StateTransitionException::class, 'Invalid state transition from "completed" to "pending"');
});

it('throws exception when updating item in cancelled transfer', function (): void {
    $transfer = StockTransfer::factory()->create([
        'status' => StockTransferStatusEnum::Cancelled,
    ]);
    $item = StockTransferItem::factory()->forStockTransfer($transfer)->create();

    $action = resolve(UpdateStockTransferItem::class);

    $data = new UpdateStockTransferItemData(
        batch_id: Optional::create(),
        quantity: 20,
    );

    expect(fn () => $action->handle($item, $data))->toThrow(StateTransitionException::class, 'Invalid state transition from "cancelled" to "pending"');
});
