<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\CompletePurchaseReturnAction;
use App\Data\PurchaseReturn\CompletePurchaseReturnData;
use App\Enums\ReturnStatusEnum;
use App\Models\Batch;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

it('completes a pending purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create();

    $action = resolve(CompletePurchaseReturnAction::class);

    $result = $action->handle($purchaseReturn, new CompletePurchaseReturnData(
        note: 'Completed return',
    ));

    expect($result->status)->toBe(ReturnStatusEnum::Completed);
});

it('removes stock from batches when completing return', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompletePurchaseReturnAction::class);

    $action->handle($purchaseReturn, new CompletePurchaseReturnData());

    expect($batch->fresh()->quantity)->toBe(90);
});

it('throws exception when completing non-pending return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create();

    $action = resolve(CompletePurchaseReturnAction::class);

    $action->handle($purchaseReturn, new CompletePurchaseReturnData());
})->throws(RuntimeException::class, 'cannot be completed');

it('throws exception when insufficient stock', function (): void {
    $batch = Batch::factory()->withQuantity(5)->create();
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompletePurchaseReturnAction::class);

    $action->handle($purchaseReturn, new CompletePurchaseReturnData());
})->throws(RuntimeException::class, 'Insufficient stock');
