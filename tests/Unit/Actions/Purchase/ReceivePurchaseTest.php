<?php

declare(strict_types=1);

use App\Actions\Purchase\ReceivePurchase;
use App\Enums\PurchaseStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;

it('may receive a pending purchase', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 1000,
    ]);

    $action = resolve(ReceivePurchase::class);

    $receivedPurchase = $action->handle($purchase);

    expect($receivedPurchase->status)->toBe(PurchaseStatusEnum::Received);
});

it('may receive an ordered purchase', function (): void {
    $purchase = Purchase::factory()->ordered()->create();
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_cost' => 100,
    ]);

    $action = resolve(ReceivePurchase::class);

    $receivedPurchase = $action->handle($purchase);

    expect($receivedPurchase->status)->toBe(PurchaseStatusEnum::Received);
});

it('creates new batch for each item', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 15,
        'unit_cost' => 200,
    ]);

    $action = resolve(ReceivePurchase::class);

    $action->handle($purchase);

    $batch = Batch::query()->where('product_id', $product->id)->first();

    expect($batch)->not->toBeNull()
        ->and($batch->warehouse_id)->toBe($purchase->warehouse_id)
        ->and($batch->quantity)->toBe(15)
        ->and($batch->cost_amount)->toBe(200)
        ->and($batch->batch_number)->toStartWith('BAT-');
});

it('updates purchase item with batch and received quantity', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product = Product::factory()->create();

    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 20,
        'received_quantity' => 0,
    ]);

    $action = resolve(ReceivePurchase::class);

    $action->handle($purchase);

    $item->refresh();

    expect($item->received_quantity)->toBe(20)
        ->and($item->batch_id)->not->toBeNull();
});

it('records stock movement for received items', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 25,
    ]);

    $action = resolve(ReceivePurchase::class);

    $action->handle($purchase);

    $movement = StockMovement::query()
        ->where('reference_type', Purchase::class)
        ->where('reference_id', $purchase->id)
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe(StockMovementTypeEnum::In)
        ->and($movement->quantity)->toBe(25)
        ->and($movement->previous_quantity)->toBe(0)
        ->and($movement->current_quantity)->toBe(25);
});

it('creates multiple batches for multiple items', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product1->id,
        'quantity' => 10,
    ]);

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product2->id,
        'quantity' => 20,
    ]);

    $action = resolve(ReceivePurchase::class);

    $action->handle($purchase);

    $batchCount = Batch::query()
        ->whereIn('product_id', [$product1->id, $product2->id])
        ->count();

    expect($batchCount)->toBe(2);
});
