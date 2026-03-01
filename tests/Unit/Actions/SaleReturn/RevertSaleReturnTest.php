<?php

declare(strict_types=1);

use App\Actions\SaleReturn\RevertSaleReturn;
use App\Data\SaleReturn\RevertSaleReturnData;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;

it('cancels a completed sale return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();

    $action = resolve(RevertSaleReturn::class);

    $result = $action->handle($saleReturn, new RevertSaleReturnData(
        note: 'Cancelled',
    ));

    expect($result->status)->toBe(ReturnStatusEnum::Pending)
        ->and($result->note)->toBe('Cancelled');
});

it('removes stock when cancelling completed return', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $saleReturn = SaleReturn::factory()->completed()->create();
    SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(RevertSaleReturn::class);

    $action->handle($saleReturn, new RevertSaleReturnData());

    expect($batch->fresh()->quantity)->toBe(90);
});

it('throws exception when cancelling return with refunds', function (): void {
    $paymentMethod = App\Models\PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);
    Payment::factory()->forSaleReturn($saleReturn)->create([
        'payment_method_id' => $paymentMethod->id,
        'amount' => -500,
    ]);

    $action = resolve(RevertSaleReturn::class);

    $action->handle($saleReturn, new RevertSaleReturnData());
})->throws(RefundNotAllowedException::class, 'Cannot refund sale return. Cannot cancel a sale return that has existing refunds. Please void the refunds first.');

it('throws exception when cancelling already cancelled return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();

    $action = resolve(RevertSaleReturn::class);

    $action->handle($saleReturn, new RevertSaleReturnData());

    $action->handle($saleReturn, new RevertSaleReturnData());
})->throws(StateTransitionException::class, 'Invalid state transition from "pending" to "Pending"');

it('throws exception when insufficient stock on cancellation', function (): void {
    $batch = Batch::factory()->withQuantity(5)->create();
    $saleReturn = SaleReturn::factory()->completed()->create();
    SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(RevertSaleReturn::class);

    $action->handle($saleReturn, new RevertSaleReturnData());
})->throws(InsufficientStockException::class, 'Insufficient stock');

it('skips items without batch when cancelling completed return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();
    SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'batch_id' => null,
        'quantity' => 10,
    ]);

    $action = resolve(RevertSaleReturn::class);

    $result = $action->handle($saleReturn, new RevertSaleReturnData());

    expect($result->status)->toBe(ReturnStatusEnum::Pending);
});
