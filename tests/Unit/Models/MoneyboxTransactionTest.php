<?php

declare(strict_types=1);

use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create(['created_by' => $user->id]);

    $transaction = MoneyboxTransaction::factory()->create([
        'created_by' => $user->id,
        'moneybox_id' => $moneybox->id,
    ])->refresh();

    expect(array_keys($transaction->toArray()))
        ->toBe([
            'id',
            'moneybox_id',
            'type',
            'amount',
            'balance_after',
            'reference',
            'notes',
            'payment_id',
            'expense_id',
            'transfer_to_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('transaction relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $moneybox = Moneybox::factory()->create(['created_by' => $user->id]);
    $payment = Payment::factory()->create(['moneybox_id' => $moneybox->id, 'created_by' => $user->id]);
    $expense = Expense::factory()->create(['moneybox_id' => $moneybox->id, 'created_by' => $user->id]);
    $transaction = MoneyboxTransaction::factory()->create([
        'created_by' => $user->id,
        'moneybox_id' => $moneybox->id,
        'payment_id' => $payment->id,
        'expense_id' => $expense->id,
        'transfer_to_id' => $moneybox->id,
    ]);
    $transaction->update(['updated_by' => $user->id]);

    expect($transaction->moneybox->id)->toBe($moneybox->id)
        ->and($transaction->transferTo->id)->toBe($moneybox->id)
        ->and($transaction->payment->id)->toBe($payment->id)
        ->and($transaction->expense->id)->toBe($expense->id)
        ->and($transaction->creator->id)->toBe($user->id)
        ->and($transaction->updater->id)->toBe($user->id);
});

test('transaction type', function (): void {
    $user = User::factory()->create()->refresh();
    $moneybox = Moneybox::factory()->create(['created_by' => $user->id]);
    $transaction = MoneyboxTransaction::factory()->create([
        'created_by' => $user->id,
        'moneybox_id' => $moneybox->id,
        'type' => MoneyboxTransactionTypeEnum::IN->value,
    ]);
    expect($transaction->isIncoming())->toBeTrue()
        ->and($transaction->isOutgoing())->toBeFalse()
        ->and($transaction->isTransfer())->toBeFalse();
});
