<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\RevertPurchaseReturn;
use App\Data\PurchaseReturn\RevertPurchaseReturnData;
use App\Enums\PaymentStateEnum;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

it('cancels a completed purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();

    $action = resolve(RevertPurchaseReturn::class);

    $result = $action->handle($purchaseReturn, new RevertPurchaseReturnData());

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

    $action = resolve(RevertPurchaseReturn::class);

    $action->handle($purchaseReturn, new RevertPurchaseReturnData());

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

    $action = resolve(RevertPurchaseReturn::class);

    $action->handle($purchaseReturn, new RevertPurchaseReturnData());
})->throws(RefundNotAllowedException::class, 'Cannot refund purchase return. Cannot cancel a purchase return that has existing refunds. Please void the refunds first.');

it('throws exception when cancelling non-completed return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();

    $action = resolve(RevertPurchaseReturn::class);

    $action->handle($purchaseReturn, new RevertPurchaseReturnData());
})->throws(StateTransitionException::class, 'Invalid state transition from "pending" to "Pending"');

it('allows revert when refunds are voided', function (): void {
    $paymentMethod = App\Models\PaymentMethod::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);
    Payment::factory()->forPurchaseReturn($purchaseReturn)->create([
        'payment_method_id' => $paymentMethod->id,
        'amount' => -500,
        'status' => PaymentStateEnum::Voided,
    ]);

    $action = resolve(RevertPurchaseReturn::class);

    $result = $action->handle($purchaseReturn, new RevertPurchaseReturnData());

    expect($result->status)->toBe(ReturnStatusEnum::Pending);
});

it('skips items without batch when cancelling completed return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();
    PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'batch_id' => null,
        'quantity' => 10,
    ]);

    $action = resolve(RevertPurchaseReturn::class);

    $result = $action->handle($purchaseReturn, new RevertPurchaseReturnData());

    expect($result->status)->toBe(ReturnStatusEnum::Pending);
});
