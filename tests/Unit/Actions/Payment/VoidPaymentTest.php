<?php

declare(strict_types=1);

use App\Actions\Payment\VoidPayment;
use App\Data\Payment\VoidPaymentData;
use App\Enums\PaymentStateEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;

$paymentMethod = null;
$user = null;

beforeEach(function () use (&$paymentMethod, &$user): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $user = User::factory()->create();
});

it('voids an active payment', function () use (&$user): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forSale($sale)->create([
        'amount' => 1000,
    ]);

    $action = resolve(VoidPayment::class);

    $voidedPayment = $action->handle($payment, new VoidPaymentData(
        void_reason: 'Customer requested cancellation',
    ), $user->id);

    expect($voidedPayment->status)->toBe(PaymentStateEnum::Voided)
        ->and($voidedPayment->voided_by)->toBe($user->id)
        ->and($voidedPayment->voided_at)->not->toBeNull()
        ->and($voidedPayment->void_reason)->toBe('Customer requested cancellation');
});

it('updates sale paid_amount after voiding payment', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forSale($sale)->create([
        'amount' => 1000,
    ]);

    $sale->forceFill([
        'paid_amount' => 1000,
        'payment_status' => PaymentStatusEnum::Paid,
    ])->save();

    $payment = $sale->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test void',
    ), $user->id);

    expect($sale->fresh()->paid_amount)->toBe(0)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('resets change_amount to zero when voiding sale without overpayment', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 1000,
        'change_amount' => 0,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 1000]);

    $payment = $sale->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test void',
    ), $user->id);

    expect($sale->fresh()->paid_amount)->toBe(0)
        ->and($sale->fresh()->change_amount)->toBe(0)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('throws exception when voiding already voided payment', function (): void {
    $user = User::factory()->create();
    $payment = Payment::factory()->voided()->create();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test',
    ), $user->id);
})->throws(RuntimeException::class, 'Payment cannot be voided. Current status: voided');

it('updates payment status to partial when one of multiple payments is voided', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 600]);
    Payment::factory()->forSale($sale)->create(['amount' => 400]);

    $sale->forceFill([
        'paid_amount' => 1000,
        'payment_status' => PaymentStatusEnum::Paid,
    ])->save();

    $payments = $sale->payments()->get();
    $firstPayment = $payments->first();

    $action = resolve(VoidPayment::class);

    $action->handle($firstPayment, new VoidPaymentData(
        void_reason: 'Test',
    ), $user->id);

    expect($sale->fresh()->paid_amount)->toBe(400)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial);
});

it('resets change_amount to zero when voiding payment from overpaid sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 1200,
        'change_amount' => 200,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 1200]);

    $payment = $sale->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test',
    ), $user->id);

    expect($sale->fresh()->paid_amount)->toBe(0)
        ->and($sale->fresh()->change_amount)->toBe(0)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('handles voiding payment when sale has change amount', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 1500,
        'change_amount' => 500,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 1500]);

    $payment = $sale->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test',
    ), $user->id);

    expect($sale->fresh()->paid_amount)->toBe(0)
        ->and($sale->fresh()->change_amount)->toBe(0);
});

it('sets change_amount when voiding leaves overpayment', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 1200]);

    $sale->forceFill([
        'paid_amount' => 1000,
        'change_amount' => 200,
    ])->save();

    $payment = $sale->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test',
    ), $user->id);

    expect($sale->fresh()->paid_amount)->toBe(0)
        ->and($sale->fresh()->change_amount)->toBe(0)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('sets change_amount when remaining active payments exceed total after voiding', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 100]);
    Payment::factory()->forSale($sale)->create(['amount' => 1100]);

    $sale->forceFill([
        'paid_amount' => 1200,
        'change_amount' => 200,
    ])->save();

    $payments = $sale->payments()->get();
    $firstPayment = $payments->first();

    $action = resolve(VoidPayment::class);

    $action->handle($firstPayment, new VoidPaymentData(
        void_reason: 'Test',
    ), $user->id);

    expect($sale->fresh()->paid_amount)->toBe(1000)
        ->and($sale->fresh()->change_amount)->toBe(100)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('voids payment for sale return', function (): void {
    $user = User::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forSaleReturn($saleReturn)->create([
        'amount' => 1000,
    ]);

    $saleReturn->forceFill([
        'paid_amount' => 1000,
        'payment_status' => PaymentStatusEnum::Paid,
    ])->save();

    $payment = $saleReturn->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test void',
    ), $user->id);

    expect($saleReturn->fresh()->paid_amount)->toBe(0)
        ->and($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('voids payment for purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forPurchase($purchase)->create([
        'amount' => 1000,
    ]);

    $purchase->forceFill([
        'paid_amount' => 1000,
        'payment_status' => PaymentStatusEnum::Paid,
    ])->save();

    $payment = $purchase->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test void',
    ), $user->id);

    expect($purchase->fresh()->paid_amount)->toBe(0)
        ->and($purchase->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('voids payment for purchase return', function (): void {
    $user = User::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forPurchaseReturn($purchaseReturn)->create([
        'amount' => 1000,
    ]);

    $purchaseReturn->forceFill([
        'paid_amount' => 1000,
        'payment_status' => PaymentStatusEnum::Paid,
    ])->save();

    $payment = $purchaseReturn->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test void',
    ), $user->id);

    expect($purchaseReturn->fresh()->paid_amount)->toBe(0)
        ->and($purchaseReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('does not update change_amount when voiding sale return payment', function (): void {
    $user = User::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forSaleReturn($saleReturn)->create([
        'amount' => 1000,
    ]);

    $saleReturn->forceFill([
        'paid_amount' => 1000,
        'payment_status' => PaymentStatusEnum::Paid,
    ])->save();

    $payment = $saleReturn->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test void',
    ), $user->id);

    expect($saleReturn->fresh()->paid_amount)->toBe(0)
        ->and($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('does not update change_amount when voiding purchase payment', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    Payment::factory()->forPurchase($purchase)->create([
        'amount' => 1000,
    ]);

    $purchase->forceFill([
        'paid_amount' => 1000,
        'payment_status' => PaymentStatusEnum::Paid,
    ])->save();

    $payment = $purchase->payments()->first();

    $action = resolve(VoidPayment::class);

    $action->handle($payment, new VoidPaymentData(
        void_reason: 'Test void',
    ), $user->id);

    expect($purchase->fresh()->paid_amount)->toBe(0)
        ->and($purchase->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});
