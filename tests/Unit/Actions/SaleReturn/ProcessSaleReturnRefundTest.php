<?php

declare(strict_types=1);

use App\Actions\SaleReturn\ProcessSaleReturnRefund;
use App\Data\SaleReturn\RefundSaleReturnData;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\RefundNotAllowedException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\SaleReturn;

it('processes refund for completed sale return', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessSaleReturnRefund::class);

    $payment = $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
        user_id: null,
        note: 'Refund issued',
    ));

    expect($payment)
        ->toBeInstanceOf(Payment::class)
        ->and($payment->amount)->toBe(-500)
        ->and($payment->reference_no)->toStartWith('PAY-');
});

it('updates payment status after refund', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessSaleReturnRefund::class);

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
    ));

    expect($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial)
        ->and($saleReturn->fresh()->paid_amount)->toBe(500);
});

it('updates to paid status when fully refunded', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessSaleReturnRefund::class);

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
    ));

    expect($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('throws exception when refunding non-completed return', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->pending()->create();

    $action = resolve(ProcessSaleReturnRefund::class);

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
    ));
})->throws(RefundNotAllowedException::class, 'Cannot refund sale return. Sale return must be completed before issuing a refund.');

it('throws exception when over-refunding', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 500,
    ]);

    $action = resolve(ProcessSaleReturnRefund::class);

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
    ));
})->throws(RefundNotAllowedException::class, 'Cannot refund sale return. Refund amount exceeds remaining refundable amount. Maximum: 500');

it('allows cumulative refunds up to total', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessSaleReturnRefund::class);

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
    ));

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
    ));

    expect($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('throws exception for zero refund amount', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create();

    $action = resolve(ProcessSaleReturnRefund::class);

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 0,
        payment_date: now(),
    ));
})->throws(RefundNotAllowedException::class, 'Cannot refund sale return. Refund amount must be greater than zero.');

it('throws exception for negative refund amount', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessSaleReturnRefund::class);

    $action->handle($saleReturn, new RefundSaleReturnData(
        payment_method_id: $paymentMethod->id,
        amount: -100,
        payment_date: now(),
    ));
})->throws(RefundNotAllowedException::class, 'Cannot refund sale return. Refund amount must be greater than zero.');

it('returns unpaid status when no negative payments exist', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $reflection = new ReflectionClass(ProcessSaleReturnRefund::class);
    $method = $reflection->getMethod('updatePaymentStatus');

    $action = resolve(ProcessSaleReturnRefund::class);
    $method->invoke($action, $saleReturn);

    expect($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});
