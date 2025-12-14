<?php

declare(strict_types=1);

use App\Enums\StockTransferStatusEnum;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    $stockTransfer = StockTransfer::factory()->create([
        'created_by' => $user->id,
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
    ])->refresh();

    expect(array_keys($stockTransfer->toArray()))
        ->toBe([
            'id',
            'reference',
            'status',
            'notes',
            'from_store_id',
            'to_store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('stock transfer relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $transfer = StockTransfer::factory()->create([
        'created_by' => $user->id,
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
    ])->refresh();
    $transfer->update(['updated_by' => $user->id]);

    $item = StockTransferItem::factory()->create([
        'stock_transfer_id' => $transfer->id,
        'product_id' => $product->id,
    ]);

    $movement = StockMovement::factory()->create([
        'source_type' => StockTransfer::class,
        'source_id' => $transfer->id,
        'product_id' => $product->id,
        'store_id' => $fromStore->id,
        'created_by' => $user->id,
    ]);

    expect($transfer->creator->id)->toBe($user->id)
        ->and($transfer->updater->id)->toBe($user->id)
        ->and($transfer->fromStore->id)->toBe($fromStore->id)
        ->and($transfer->toStore->id)->toBe($toStore->id)
        ->and($transfer->items->count())->toBe(1)
        ->and($transfer->items->first()->id)->toBe($item->id)
        ->and($transfer->stockMovements->count())->toBe(1)
        ->and($transfer->stockMovements->first()->id)->toBe($movement->id);
});

test('stock transfer status', function (): void {
    $user = User::factory()->create()->refresh();
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);
    $transfer = StockTransfer::factory()->create([
        'created_by' => $user->id,
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
        'status' => StockTransferStatusEnum::PENDING->value,
    ])->refresh();

    expect($transfer->isPending())->toBeTrue()
        ->and($transfer->isCompleted())->toBeFalse()
        ->and($transfer->isCancelled())->toBeFalse();

    $transfer->update(['status' => StockTransferStatusEnum::COMPLETED->value]);
    expect($transfer->isPending())->toBeFalse()
        ->and($transfer->isCompleted())->toBeTrue()
        ->and($transfer->isCancelled())->toBeFalse();

    $transfer->update(['status' => StockTransferStatusEnum::CANCELLED->value]);
    expect($transfer->isPending())->toBeFalse()
        ->and($transfer->isCompleted())->toBeFalse()
        ->and($transfer->isCancelled())->toBeTrue();
});
