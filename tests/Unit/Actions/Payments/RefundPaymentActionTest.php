<?php

declare(strict_types=1);

use App\Actions\Payments\RefundPayment;
use App\Data\Payments\RefundPaymentData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\User;

it('may refund a payment partially', function (): void {
    $user = User::factory()->create();
    $originalPayment = Payment::factory()->create([
        'amount' => 100000,
        'method' => PaymentMethodEnum::CASH,
        'reference' => 'PAY-001',
        'created_by' => $user->id,
    ]);

    $action = resolve(RefundPayment::class);

    $data = RefundPaymentData::from([
        'original_payment_id' => $originalPayment->id,
        'amount' => 30000,
        'moneybox_id' => null,
        'reason' => 'Customer request',
        'notes' => 'Partial refund',
        'created_by' => $user->id,
    ]);

    $refund = $action->handle($data);

    expect($refund)->toBeInstanceOf(Payment::class)
        ->and($refund->amount)->toBe(-30000)
        ->and($refund->method)->toBe(PaymentMethodEnum::CASH)
        ->and($refund->reference)->toContain('REFUND-PAY-001')
        ->and($refund->notes)->toContain('Customer request')
        ->and($refund->created_by)->toBe($user->id);
});

it('may refund full payment amount', function (): void {
    $user = User::factory()->create();
    $originalPayment = Payment::factory()->create([
        'amount' => 50000,
        'method' => PaymentMethodEnum::CARD,
        'reference' => 'PAY-002',
        'created_by' => $user->id,
    ]);

    $action = resolve(RefundPayment::class);

    $data = RefundPaymentData::from([
        'original_payment_id' => $originalPayment->id,
        'amount' => 50000,
        'moneybox_id' => null,
        'reason' => 'Order cancelled',
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $refund = $action->handle($data);

    expect($refund->amount)->toBe(-50000);
});

it('may refund payment with moneybox', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 100000,
        'created_by' => $user->id,
    ]);
    $originalPayment = Payment::factory()->create([
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH,
        'moneybox_id' => $moneybox->id,
        'reference' => 'PAY-003',
        'created_by' => $user->id,
    ]);

    $action = resolve(RefundPayment::class);

    $data = RefundPaymentData::from([
        'original_payment_id' => $originalPayment->id,
        'amount' => 20000,
        'moneybox_id' => $moneybox->id,
        'reason' => 'Refund request',
        'notes' => 'From cash register',
        'created_by' => $user->id,
    ]);

    $refund = $action->handle($data);

    expect($refund->moneybox_id)->toBe($moneybox->id);

    // Check moneybox transaction (OUT)
    $transaction = MoneyboxTransaction::query()
        ->where('payment_id', $refund->id)
        ->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(MoneyboxTransactionTypeEnum::OUT)
        ->and($transaction->amount)->toBe(20000)
        ->and($transaction->balance_after)->toBe(80000);

    $moneybox->refresh();
    expect($moneybox->balance)->toBe(80000);
});

it('throws exception when refund exceeds original amount', function (): void {
    $user = User::factory()->create();
    $originalPayment = Payment::factory()->create([
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH,
        'created_by' => $user->id,
    ]);

    $action = resolve(RefundPayment::class);

    $data = RefundPaymentData::from([
        'original_payment_id' => $originalPayment->id,
        'amount' => 60000,
        'moneybox_id' => null,
        'reason' => null,
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $action->handle($data);
})->throws(InvalidArgumentException::class, 'Refund amount cannot exceed original payment amount');
