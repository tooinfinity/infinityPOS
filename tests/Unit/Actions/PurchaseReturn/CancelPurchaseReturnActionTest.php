<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\CancelPurchaseReturnAction;
use App\Data\PurchaseReturn\CancelPurchaseReturnData;
use App\Enums\ReturnStatusEnum;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

it('cancels a completed purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();

    $action = resolve(CancelPurchaseReturnAction::class);

    $result = $action->handle($purchaseReturn, new CancelPurchaseReturnData());

    expect($result->status)->toBe(ReturnStatusEnum::Pending);
});

it('adds stock back when cancelling completed return', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CancelPurchaseReturnAction::class);

    $action->handle($purchaseReturn, new CancelPurchaseReturnData());

    expect($batch->fresh()->quantity)->toBe(110);
});

it('throws exception when cancelling return with refunds', function (): void {
    $paymentMethod = App\Models\PaymentMethod::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);
    Payment::factory()->forPurchaseReturn($purchaseReturn)->create([
        'payment_method_id' => $paymentMethod->id,
        'amount' => -500,
    ]);

    $action = resolve(CancelPurchaseReturnAction::class);

    $action->handle($purchaseReturn, new CancelPurchaseReturnData());
})->throws(RuntimeException::class, 'existing refunds');
