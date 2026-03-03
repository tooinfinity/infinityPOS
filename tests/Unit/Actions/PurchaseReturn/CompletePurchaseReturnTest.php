<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\CompletePurchaseReturn;
use App\Data\PurchaseReturn\CompletePurchaseReturnData;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

it('removes stock from batches when completing return', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompletePurchaseReturn::class);

    $action->handle($purchaseReturn, new CompletePurchaseReturnData());

    expect($batch->fresh()->quantity)->toBe(90);
});

it('throws exception when completing non-pending return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create();

    $action = resolve(CompletePurchaseReturn::class);

    $action->handle($purchaseReturn, new CompletePurchaseReturnData());
})->throws(StateTransitionException::class, 'Invalid state transition from "PurchaseReturn (completed)" to "completed"');

it('throws exception when insufficient stock', function (): void {
    $batch = Batch::factory()->withQuantity(5)->create();
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompletePurchaseReturn::class);

    $action->handle($purchaseReturn, new CompletePurchaseReturnData());
})->throws(InsufficientStockException::class, 'Insufficient stock');

it('skips items without batch when completing return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'batch_id' => null,
        'quantity' => 10,
    ]);

    $action = resolve(CompletePurchaseReturn::class);

    $result = $action->handle($purchaseReturn, new CompletePurchaseReturnData());

    expect($result->status)->toBe(App\Enums\ReturnStatusEnum::Completed);
});

it('throws exception when completing return without items', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();

    $action = resolve(CompletePurchaseReturn::class);

    $action->handle($purchaseReturn, new CompletePurchaseReturnData());
})->throws(InvalidOperationException::class, 'Purchase return cannot be completed without items');

it('updates note when provided in data', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $purchaseReturn = PurchaseReturn::factory()->pending()->create(['note' => 'Original note']);
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompletePurchaseReturn::class);

    $result = $action->handle($purchaseReturn, new CompletePurchaseReturnData(
        note: 'Updated note during completion'
    ));

    expect($result->note)->toBe('Updated note during completion');
});

it('keeps original note when not provided in data', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $purchaseReturn = PurchaseReturn::factory()->pending()->create(['note' => 'Original note']);
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompletePurchaseReturn::class);

    $result = $action->handle($purchaseReturn, new CompletePurchaseReturnData());

    expect($result->note)->toBe('Original note');
});
