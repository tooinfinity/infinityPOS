<?php

declare(strict_types=1);

use App\Actions\Payment\UnvoidPayment;
use App\Enums\PaymentStateEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;

it('unvoids a voided payment', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forSale($sale)->voided()->create([
        'amount' => 1000,
        'voided_by' => $user->id,
        'void_reason' => 'Previous void',
    ]);

    $action = resolve(UnvoidPayment::class);

    $unvoidedPayment = $action->handle($payment);

    expect($unvoidedPayment->status)->toBe(PaymentStateEnum::Active)
        ->and($unvoidedPayment->voided_by)->toBeNull()
        ->and($unvoidedPayment->voided_at)->toBeNull()
        ->and($unvoidedPayment->void_reason)->toBeNull();
});

it('updates sale paid_amount after unvoiding payment', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forSale($sale)->voided()->create([
        'amount' => 1000,
    ]);

    $action = resolve(UnvoidPayment::class);

    $action->handle($payment);

    expect($sale->fresh()->paid_amount)->toBe(1000)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('throws exception when unvoiding active payment', function (): void {
    $payment = Payment::factory()->create();

    $action = resolve(UnvoidPayment::class);

    $action->handle($payment);
})->throws(RuntimeException::class, 'Payment cannot be unvoided. Current status: active');

it('updates payment status to partial when unvoiding one of multiple payments', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 400,
    ]);

    Payment::factory()->forSale($sale)->voided()->create(['amount' => 600]);
    Payment::factory()->forSale($sale)->create(['amount' => 400]);

    $voidedPayment = $sale->payments()->where('status', PaymentStateEnum::Voided)->first();

    $action = resolve(UnvoidPayment::class);

    $action->handle($voidedPayment);

    expect($sale->fresh()->paid_amount)->toBe(1000)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('sets payment status to partial when unvoiding results in partial payment', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 200,
    ]);

    $activePayment = Payment::factory()->forSale($sale)->create(['amount' => 200]);
    $voidedPayment = Payment::factory()->forSale($sale)->voided()->create(['amount' => 300]);

    $action = resolve(UnvoidPayment::class);

    $action->handle($voidedPayment);

    expect($sale->fresh()->paid_amount)->toBe(500)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial);
});

it('sets payment status to unpaid when all payments are voided', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    $voidedPayment = Payment::factory()->forSale($sale)->voided()->create(['amount' => 1000]);

    $action = resolve(UnvoidPayment::class);

    $action->handle($voidedPayment);

    expect($sale->fresh()->paid_amount)->toBe(1000)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('sets change_amount when unvoiding results in overpayment', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 700,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 700]);
    Payment::factory()->forSale($sale)->voided()->create(['amount' => 500]);

    $voidedPayment = $sale->payments()->where('status', PaymentStateEnum::Voided)->first();

    $action = resolve(UnvoidPayment::class);

    $action->handle($voidedPayment);

    expect($sale->fresh()->paid_amount)->toBe(1000)
        ->and($sale->fresh()->change_amount)->toBe(200)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('unvoids payment for sale return', function (): void {
    $user = User::factory()->create();
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forSaleReturn($saleReturn)->voided()->create([
        'amount' => 1000,
        'voided_by' => $user->id,
        'void_reason' => 'Previous void',
    ]);

    $action = resolve(UnvoidPayment::class);

    $unvoidedPayment = $action->handle($payment);

    expect($unvoidedPayment->status)->toBe(PaymentStateEnum::Active)
        ->and($saleReturn->fresh()->paid_amount)->toBe(1000)
        ->and($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('unvoids payment for purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forPurchase($purchase)->voided()->create([
        'amount' => 1000,
        'voided_by' => $user->id,
        'void_reason' => 'Previous void',
    ]);

    $action = resolve(UnvoidPayment::class);

    $unvoidedPayment = $action->handle($payment);

    expect($unvoidedPayment->status)->toBe(PaymentStateEnum::Active)
        ->and($purchase->fresh()->paid_amount)->toBe(1000)
        ->and($purchase->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('unvoids payment for purchase return', function (): void {
    $user = User::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forPurchaseReturn($purchaseReturn)->voided()->create([
        'amount' => 1000,
        'voided_by' => $user->id,
        'void_reason' => 'Previous void',
    ]);

    $action = resolve(UnvoidPayment::class);

    $unvoidedPayment = $action->handle($payment);

    expect($unvoidedPayment->status)->toBe(PaymentStateEnum::Active)
        ->and($purchaseReturn->fresh()->paid_amount)->toBe(1000)
        ->and($purchaseReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('does not update change_amount when unvoiding sale return payment', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forSaleReturn($saleReturn)->voided()->create([
        'amount' => 1000,
    ]);

    $action = resolve(UnvoidPayment::class);

    $action->handle($payment);

    expect($saleReturn->fresh()->paid_amount)->toBe(1000)
        ->and($saleReturn->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('does not update change_amount when unvoiding purchase payment', function (): void {
    $purchase = Purchase::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
    ]);

    $payment = Payment::factory()->forPurchase($purchase)->voided()->create([
        'amount' => 1000,
    ]);

    $action = resolve(UnvoidPayment::class);

    $action->handle($payment);

    expect($purchase->fresh()->paid_amount)->toBe(1000)
        ->and($purchase->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('resets change_amount to zero when unvoiding sale without overpayment', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 700,
        'change_amount' => 200,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 700]);
    $voidedPayment = Payment::factory()->forSale($sale)->voided()->create(['amount' => 300]);

    $action = resolve(UnvoidPayment::class);

    $action->handle($voidedPayment);

    expect($sale->fresh()->paid_amount)->toBe(1000)
        ->and($sale->fresh()->change_amount)->toBe(0)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('handles unvoiding zero-amount payment resulting in unpaid status', function (): void {
    $sale = Sale::factory()->completed()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    $payment = Payment::factory()->forSale($sale)->voided()->create([
        'amount' => 0,
    ]);

    $action = resolve(UnvoidPayment::class);

    $action->handle($payment);

    expect($sale->fresh()->paid_amount)->toBe(0)
        ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});
