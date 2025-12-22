<?php

declare(strict_types=1);

use App\Actions\Inventory\CancelStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;

it('may cancel a pending stock transfer', function (): void {
    $user = User::factory()->create();
    $transfer = StockTransfer::factory()->pending()->create(['created_by' => $user->id]);

    $action = resolve(CancelStockTransfer::class);

    $cancelledTransfer = $action->handle($transfer, $user->id);

    expect($cancelledTransfer->status)->toBe(StockTransferStatusEnum::CANCELLED)
        ->and($cancelledTransfer->updated_by)->toBe($user->id);
});

it('may cancel a completed transfer and reverse stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    // Create layers in both stores
    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $fromStore->id,
        'received_qty' => 70,
        'remaining_qty' => 70,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $toStore->id,
        'received_qty' => 30,
        'remaining_qty' => 30,
    ]);

    $transfer = StockTransfer::factory()->completed()->create([
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
        'created_by' => $user->id,
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'quantity' => 30,
        'batch_number' => null,
    ]);

    $action = resolve(CancelStockTransfer::class);

    $cancelledTransfer = $action->handle($transfer, $user->id);

    expect($cancelledTransfer->status)->toBe(StockTransferStatusEnum::CANCELLED);

    // Check reversal movements were created
    $movements = StockMovement::query()
        ->where('source_type', StockTransfer::class)
        ->where('source_id', $transfer->id)
        ->where('notes', 'like', '%cancelled%')
        ->get();
    expect($movements)->toHaveCount(2);
});

it('returns already cancelled transfer', function (): void {
    $user = User::factory()->create();
    $transfer = StockTransfer::factory()->cancelled()->create(['created_by' => $user->id]);

    $action = resolve(CancelStockTransfer::class);

    $cancelledTransfer = $action->handle($transfer, $user->id);

    expect($cancelledTransfer->status)->toBe(StockTransferStatusEnum::CANCELLED);
});
