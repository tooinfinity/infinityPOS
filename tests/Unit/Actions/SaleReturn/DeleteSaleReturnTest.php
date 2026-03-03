<?php

declare(strict_types=1);

use App\Actions\SaleReturn\DeleteSaleReturn;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\Payment;
use App\Models\SaleReturn;

it('deletes a pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();

    $action = resolve(DeleteSaleReturn::class);

    $action->handle($saleReturn);

    expect(SaleReturn::query()->find($saleReturn->id))->toBeNull();
});

it('deletes sale return items when deleting', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();

    App\Models\SaleReturnItem::factory()->forSaleReturn($saleReturn)->create();
    App\Models\SaleReturnItem::factory()->forSaleReturn($saleReturn)->create();

    expect($saleReturn->items()->count())->toBe(2);

    $action = resolve(DeleteSaleReturn::class);

    $action->handle($saleReturn);

    expect(SaleReturn::query()->find($saleReturn->id))->toBeNull();
});

it('throws exception when deleting non-pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();

    $action = resolve(DeleteSaleReturn::class);

    $action->handle($saleReturn);
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "Pending"');

it('throws exception when sale return has refunds', function (): void {
    $paymentMethod = App\Models\PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->pending()->create([
        'total_amount' => 1000,
    ]);
    Payment::factory()->forSaleReturn($saleReturn)->create([
        'payment_method_id' => $paymentMethod->id,
        'amount' => -500,
    ]);

    $action = resolve(DeleteSaleReturn::class);

    $action->handle($saleReturn);
})->throws(RefundNotAllowedException::class, 'Cannot refund sale return. Cannot delete a sale return that has existing refunds. Please void the refunds first.');
