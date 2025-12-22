<?php

declare(strict_types=1);

use App\Actions\Payments\ProcessPayment;
use App\Data\Payments\ProcessPaymentData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;

it('may process a payment without moneybox', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create(['created_by' => $user->id]);
    $action = resolve(ProcessPayment::class);

    $data = ProcessPaymentData::from([
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH,
        'moneybox_id' => null,
        'reference' => 'PAY-001',
        'notes' => 'Cash payment',
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'created_by' => $user->id,
    ]);

    $payment = $action->handle($data);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->amount)->toBe(50000)
        ->and($payment->method)->toBe(PaymentMethodEnum::CASH)
        ->and($payment->reference)->toBe('PAY-001')
        ->and($payment->related_type)->toBe(Sale::class)
        ->and($payment->related_id)->toBe($sale->id)
        ->and($payment->created_by)->toBe($user->id);
});

it('may process a payment with moneybox', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 10000,
        'created_by' => $user->id,
    ]);
    $action = resolve(ProcessPayment::class);

    $data = ProcessPaymentData::from([
        'amount' => 30000,
        'method' => PaymentMethodEnum::CASH,
        'moneybox_id' => $moneybox->id,
        'reference' => 'PAY-002',
        'notes' => 'Payment to cash register',
        'related_type' => null,
        'related_id' => null,
        'created_by' => $user->id,
    ]);

    $payment = $action->handle($data);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->moneybox_id)->toBe($moneybox->id);

    // Check moneybox transaction was created
    $transaction = MoneyboxTransaction::query()
        ->where('payment_id', $payment->id)
        ->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(MoneyboxTransactionTypeEnum::IN)
        ->and($transaction->amount)->toBe(30000)
        ->and($transaction->balance_after)->toBe(40000);

    // Check moneybox balance updated
    $moneybox->refresh();
    expect($moneybox->balance)->toBe(40000);
});

it('processes card payment', function (): void {
    $user = User::factory()->create();
    $action = resolve(ProcessPayment::class);

    $data = ProcessPaymentData::from([
        'amount' => 75000,
        'method' => PaymentMethodEnum::CARD,
        'moneybox_id' => null,
        'reference' => 'CARD-001',
        'notes' => 'Card payment',
        'related_type' => null,
        'related_id' => null,
        'created_by' => $user->id,
    ]);

    $payment = $action->handle($data);

    expect($payment->method)->toBe(PaymentMethodEnum::CARD);
});

it('processes transfer payment', function (): void {
    $user = User::factory()->create();
    $action = resolve(ProcessPayment::class);

    $data = ProcessPaymentData::from([
        'amount' => 100000,
        'method' => PaymentMethodEnum::TRANSFER,
        'moneybox_id' => null,
        'reference' => 'TRF-001',
        'notes' => 'Bank transfer',
        'related_type' => null,
        'related_id' => null,
        'created_by' => $user->id,
    ]);

    $payment = $action->handle($data);

    expect($payment->method)->toBe(PaymentMethodEnum::TRANSFER);
});
