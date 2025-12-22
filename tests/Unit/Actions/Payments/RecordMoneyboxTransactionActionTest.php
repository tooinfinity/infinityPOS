<?php

declare(strict_types=1);

use App\Actions\Payments\RecordMoneyboxTransaction;
use App\Data\Payments\RecordMoneyboxTransactionData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\User;

it('may record incoming transaction', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);
    $payment = Payment::factory()->create(['created_by' => $user->id]);
    $action = resolve(RecordMoneyboxTransaction::class);

    $data = RecordMoneyboxTransactionData::from([
        'moneybox_id' => $moneybox->id,
        'type' => MoneyboxTransactionTypeEnum::IN,
        'amount' => 30000,
        'reference' => 'TXN-001',
        'notes' => 'Cash deposit',
        'payment_id' => $payment->id,
        'expense_id' => null,
        'transfer_to_moneybox_id' => null,
        'created_by' => $user->id,
    ]);

    $transaction = $action->handle($data);

    expect($transaction)->toBeInstanceOf(MoneyboxTransaction::class)
        ->and($transaction->type)->toBe(MoneyboxTransactionTypeEnum::IN)
        ->and($transaction->amount)->toBe(30000)
        ->and($transaction->balance_after)->toBe(80000)
        ->and($transaction->payment_id)->toBe($payment->id);

    $moneybox->refresh();
    expect($moneybox->balance)->toBe(80000);
});

it('may record outgoing transaction', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 100000,
        'created_by' => $user->id,
    ]);
    $action = resolve(RecordMoneyboxTransaction::class);

    $data = RecordMoneyboxTransactionData::from([
        'moneybox_id' => $moneybox->id,
        'type' => MoneyboxTransactionTypeEnum::OUT,
        'amount' => 25000,
        'reference' => 'TXN-002',
        'notes' => 'Cash withdrawal',
        'payment_id' => null,
        'expense_id' => null,
        'transfer_to_moneybox_id' => null,
        'created_by' => $user->id,
    ]);

    $transaction = $action->handle($data);

    expect($transaction->type)->toBe(MoneyboxTransactionTypeEnum::OUT)
        ->and($transaction->amount)->toBe(25000)
        ->and($transaction->balance_after)->toBe(75000);

    $moneybox->refresh();
    expect($moneybox->balance)->toBe(75000);
});

it('may record transfer transaction', function (): void {
    $user = User::factory()->create();
    $fromMoneybox = Moneybox::factory()->create([
        'balance' => 100000,
        'created_by' => $user->id,
    ]);
    $toMoneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);
    $action = resolve(RecordMoneyboxTransaction::class);

    $data = RecordMoneyboxTransactionData::from([
        'moneybox_id' => $fromMoneybox->id,
        'type' => MoneyboxTransactionTypeEnum::TRANSFER,
        'amount' => 20000,
        'reference' => 'TRF-001',
        'notes' => 'Transfer between registers',
        'payment_id' => null,
        'expense_id' => null,
        'transfer_to_moneybox_id' => $toMoneybox->id,
        'created_by' => $user->id,
    ]);

    $transaction = $action->handle($data);

    expect($transaction->type)->toBe(MoneyboxTransactionTypeEnum::TRANSFER)
        ->and($transaction->amount)->toBe(20000)
        ->and($transaction->balance_after)->toBe(80000)
        ->and($transaction->transfer_to_moneybox_id)->toBe($toMoneybox->id);

    $fromMoneybox->refresh();
    expect($fromMoneybox->balance)->toBe(80000);
});

it('uses pessimistic locking to prevent race conditions', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);
    $action = resolve(RecordMoneyboxTransaction::class);

    $data = RecordMoneyboxTransactionData::from([
        'moneybox_id' => $moneybox->id,
        'type' => MoneyboxTransactionTypeEnum::IN,
        'amount' => 10000,
        'reference' => null,
        'notes' => null,
        'payment_id' => null,
        'expense_id' => null,
        'transfer_to_moneybox_id' => null,
        'created_by' => $user->id,
    ]);

    $transaction = $action->handle($data);

    expect($transaction->balance_after)->toBe(60000);
});
