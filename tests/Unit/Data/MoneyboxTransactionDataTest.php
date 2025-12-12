<?php

declare(strict_types=1);

use App\Data\MoneyboxTransactionData;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\User;

it('transforms an moneybox transaction model into MoneyboxTransactionData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $moneybox = Moneybox::factory()->create();
    $payment = Payment::factory()->create();
    $expense = Expense::factory()->create();
    $transferTo = Moneybox::factory()->create();

    /** @var MoneyboxTransaction $moneyboxTransaction */
    $moneyboxTransaction = MoneyboxTransaction::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($moneybox, 'moneybox')
        ->for($payment, 'payment')
        ->for($expense, 'expense')
        ->for($transferTo, 'transferTo')
        ->create();

    $data = MoneyboxTransactionData::from(
        $moneyboxTransaction->load(['creator', 'updater', 'moneybox', 'payment', 'expense', 'transferTo'])
    );

    expect($data)
        ->toBeInstanceOf(MoneyboxTransactionData::class)
        ->id->toBe($moneyboxTransaction->id)
        ->and($data->type)->toBe($moneyboxTransaction->type)
        ->and($data->creator->id)->toBe($creator->id)
        ->and($data->updater->id)->toBe($updater->id)
        ->and($data->moneybox->id)->toBe($moneybox->id)
        ->and($data->payment->id)->toBe($payment->id)
        ->and($data->expense->id)->toBe($expense->id)
        ->and($data->transferTo->id)->toBe($transferTo->id)
        ->and($data->created_at)->toBe($moneyboxTransaction->created_at->toDateTimeString())
        ->and($data->updated_at)->toBe($moneyboxTransaction->updated_at->toDateTimeString());
});
