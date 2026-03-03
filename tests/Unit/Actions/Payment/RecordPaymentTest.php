<?php

declare(strict_types=1);

use App\Actions\Payment\RecordPayment;
use App\Data\Payment\RecordPaymentData;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\InvalidPaymentMethodException;
use App\Exceptions\OverpaymentException;
use App\Exceptions\StateTransitionException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;

$paymentMethod = null;

beforeEach(function () use (&$paymentMethod): void {
    $paymentMethod = PaymentMethod::factory()->create();
});

it('records payment for completed sale', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $payment = $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
        user_id: null,
        note: 'Payment received',
    ));

    expect($payment)
        ->toBeInstanceOf(Payment::class)
        ->and($payment->amount)->toBe(1000)
        ->and($payment->reference_no)->toStartWith('PAY-');
});

it('updates paid amount and payment status', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($sale->fresh()->paid_amount)->toBe(1000)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('updates to partial payment status', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($sale->fresh()->paid_amount)->toBe(500)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial);
});

it('works with completed sale return', function () use (&$paymentMethod): void {
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $payment = $action->handle($saleReturn, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($payment->amount)->toBe(500);
});

it('works with received purchase', function () use (&$paymentMethod): void {
    $purchase = Purchase::factory()->received()->create([
        'total_amount' => 1000,
    ]);

    $action = resolve(RecordPayment::class);

    $payment = $action->handle($purchase, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($payment->amount)->toBe(1000);
});

it('throws exception for pending sale', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->pending()->create();

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(StateTransitionException::class);

it('throws exception for cancelled sale', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->cancelled()->create();

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(StateTransitionException::class);

it('generates unique payment reference', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $payment = $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1000,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($payment->reference_no)
        ->toStartWith('PAY-')
        ->and(mb_strlen($payment->reference_no))->toBeGreaterThan(10);
});

it('stores payment in database', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $payment = $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
        user_id: null,
        note: 'Test payment',
    ));

    expect(Payment::query()->find($payment->id))->not->toBeNull();
});

it('works with purchase return', function () use (&$paymentMethod): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $payment = $action->handle($purchaseReturn, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 500,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($payment->amount)->toBe(500);
});

it('sets unpaid status when amount is zero', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 0,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('throws exception for pending sale return', function () use (&$paymentMethod): void {
    $saleReturn = SaleReturn::factory()->pending()->create();

    $action = resolve(RecordPayment::class);

    $action->handle($saleReturn, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 100,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(StateTransitionException::class);

it('throws exception for pending purchase', function () use (&$paymentMethod): void {
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::Pending,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($purchase, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 100,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(StateTransitionException::class);

it('throws exception for non-existent payment method', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: 99999,
        amount: 100,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(InvalidPaymentMethodException::class);

it('throws exception for inactive payment method', function (): void {
    $inactiveMethod = PaymentMethod::factory()->create([
        'is_active' => false,
    ]);

    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $inactiveMethod->id,
        amount: 100,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(InvalidPaymentMethodException::class);

it('calculates change amount for overpaid sale', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1500,
        payment_date: now(),
        user_id: null,
        note: null,
    ));

    expect($sale->fresh()->change_amount)->toBe(500);
});

it('throws exception for sale overpayment exceeding twice total', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 1500,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(OverpaymentException::class);

it('throws exception for negative payment amount', function () use (&$paymentMethod): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($sale, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: -100,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(InvalidOperationException::class);

it('throws exception for purchase overpayment', function () use (&$paymentMethod): void {
    $purchase = Purchase::factory()->received()->create([
        'total_amount' => 500,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($purchase, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 600,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(OverpaymentException::class);

it('throws exception for purchase return overpayment', function () use (&$paymentMethod): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($purchaseReturn, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 600,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(OverpaymentException::class);

it('throws exception for sale return overpayment', function () use (&$paymentMethod): void {
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
    ]);

    $action = resolve(RecordPayment::class);

    $action->handle($saleReturn, new RecordPaymentData(
        payment_method_id: $paymentMethod->id,
        amount: 600,
        payment_date: now(),
        user_id: null,
        note: null,
    ));
})->throws(OverpaymentException::class);
