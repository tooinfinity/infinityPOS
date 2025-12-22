<?php

declare(strict_types=1);

use App\Actions\Payments\VoidPayment;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\User;

it('may void a payment without moneybox', function (): void {
    $user = User::factory()->create();
    $payment = Payment::factory()->create([
        'amount' => 75000,
        'method' => PaymentMethodEnum::CASH,
        'reference' => 'PAY-001',
        'moneybox_id' => null,
        'created_by' => $user->id,
    ]);

    $action = resolve(VoidPayment::class);

    $voidPayment = $action->handle($payment, $user->id, 'Payment error');

    expect($voidPayment)->toBeInstanceOf(Payment::class)
        ->and($voidPayment->amount)->toBe(-75000)
        ->and($voidPayment->method)->toBe(PaymentMethodEnum::CASH)
        ->and($voidPayment->reference)->toContain('VOID-PAY-001')
        ->and($voidPayment->notes)->toContain('Payment error')
        ->and($voidPayment->created_by)->toBe($user->id);
});

it('may void a payment with moneybox', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 100000,
        'created_by' => $user->id,
    ]);
    $payment = Payment::factory()->create([
        'amount' => 40000,
        'method' => PaymentMethodEnum::CASH,
        'reference' => 'PAY-002',
        'moneybox_id' => $moneybox->id,
        'created_by' => $user->id,
    ]);

    $action = resolve(VoidPayment::class);

    $voidPayment = $action->handle($payment, $user->id, 'Duplicate payment');

    expect($voidPayment->amount)->toBe(-40000)
        ->and($voidPayment->moneybox_id)->toBe($moneybox->id);

    // Check moneybox transaction (OUT to reverse)
    $transaction = MoneyboxTransaction::query()
        ->where('payment_id', $voidPayment->id)
        ->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(MoneyboxTransactionTypeEnum::OUT)
        ->and($transaction->amount)->toBe(40000)
        ->and($transaction->balance_after)->toBe(60000);

    $moneybox->refresh();
    expect($moneybox->balance)->toBe(60000);
});

it('voids payment without reason', function (): void {
    $user = User::factory()->create();
    $payment = Payment::factory()->create([
        'amount' => 25000,
        'method' => PaymentMethodEnum::CARD,
        'reference' => 'PAY-003',
        'created_by' => $user->id,
    ]);

    $action = resolve(VoidPayment::class);

    $voidPayment = $action->handle($payment, $user->id);

    expect($voidPayment->notes)->toContain('Void payment PAY-003');
});
