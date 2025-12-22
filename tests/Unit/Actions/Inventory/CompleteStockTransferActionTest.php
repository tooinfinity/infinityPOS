<?php

declare(strict_types=1);

use App\Actions\Inventory\CompleteStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;

it('may complete a pending stock transfer', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    // Create inventory layer in source store
    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $fromStore->id,
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $transfer = StockTransfer::factory()->pending()->create([
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
        'created_by' => $user->id,
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'quantity' => 30,
        'batch_number' => null,
    ]);

    $action = resolve(CompleteStockTransfer::class);

    $completedTransfer = $action->handle($transfer, $user->id);

    expect($completedTransfer->status)->toBe(StockTransferStatusEnum::COMPLETED)
        ->and($completedTransfer->updated_by)->toBe($user->id);

    // Check source store layer was deducted
    $sourceLayer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $fromStore->id)
        ->first();
    expect($sourceLayer->remaining_qty)->toBe(70);

    // Check destination store layer was created
    $destLayer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $toStore->id)
        ->first();
    expect($destLayer)->not->toBeNull()
        ->and($destLayer->remaining_qty)->toBe(30);

    // Check stock movements were created
    $movements = StockMovement::query()
        ->where('source_type', StockTransfer::class)
        ->where('source_id', $transfer->id)
        ->get();
    expect($movements)->toHaveCount(2);
});

it('cannot complete a cancelled transfer', function (): void {
    $user = User::factory()->create();
    $transfer = StockTransfer::factory()->cancelled()->create(['created_by' => $user->id]);

    $action = resolve(CompleteStockTransfer::class);

    $action->handle($transfer, $user->id);
})->throws(InvalidArgumentException::class, 'Cannot complete a cancelled transfer');

it('returns already completed transfer', function (): void {
    $user = User::factory()->create();
    $transfer = StockTransfer::factory()->completed()->create(['created_by' => $user->id]);

    $action = resolve(CompleteStockTransfer::class);

    $completedTransfer = $action->handle($transfer, $user->id);

    expect($completedTransfer->status)->toBe(StockTransferStatusEnum::COMPLETED);
});
