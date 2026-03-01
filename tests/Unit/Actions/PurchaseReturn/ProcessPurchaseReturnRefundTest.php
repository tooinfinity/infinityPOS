<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\ProcessPurchaseReturnRefund;
use App\Data\PurchaseReturn\RefundPurchaseReturnData;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\OverpaymentException;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PurchaseReturn;

it('processes refund for completed purchase return', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessPurchaseReturnRefund::class);

    $payment = $action->handle($purchaseReturn, new RefundPurchaseReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
    ));

    expect($payment)
        ->toBeInstanceOf(Payment::class)
        ->and($payment->amount)->toBe(-500);
});

it('updates payment status after refund', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessPurchaseReturnRefund::class);

    $action->handle($purchaseReturn, new RefundPurchaseReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
    ));

    expect($purchaseReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial);
});

it('throws exception when refunding non-completed return', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();

    $action = resolve(ProcessPurchaseReturnRefund::class);

    $action->handle($purchaseReturn, new RefundPurchaseReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
    ));
})->throws(RefundNotAllowedException::class, 'must be completed');

it('throws exception when over-refunding', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 500,
    ]);

    $action = resolve(ProcessPurchaseReturnRefund::class);

    $action->handle($purchaseReturn, new RefundPurchaseReturnData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
    ));
})->throws(RefundNotAllowedException::class, 'Cannot refund purchase return. Refund amount exceeds remaining refundable amount. Maximum: 500');

it('throws exception for negative refund amount', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(ProcessPurchaseReturnRefund::class);

    $action->handle($purchaseReturn, new RefundPurchaseReturnData(
        payment_method_id: $paymentMethod->id,
        amount: -100,
        payment_date: now(),
    ));
})->throws(RefundNotAllowedException::class, 'greater than zero');

it('returns unpaid status when no negative payments exist', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
    ]);

    $reflection = new ReflectionClass(ProcessPurchaseReturnRefund::class);
    $method = $reflection->getMethod('updatePaymentStatus');

    $action = resolve(ProcessPurchaseReturnRefund::class);
    $method->invoke($action, $purchaseReturn);

    expect($purchaseReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});
