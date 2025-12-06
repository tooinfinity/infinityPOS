<?php

declare(strict_types=1);

use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $moneybox = Moneybox::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($moneybox->toArray()))
        ->toBe([
            'id',
            'name',
            'type',
            'description',
            'balance',
            'bank_name',
            'account_number',
            'is_active',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('money box relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $moneybox = Moneybox::factory()->create(['store_id' => $store->id, 'created_by' => $user->id]);
    $transactions = MoneyboxTransaction::factory()->create(['moneybox_id' => $moneybox->id, 'created_by' => $user->id])->refresh();
    $payments = Payment::factory()->create(['moneybox_id' => $moneybox->id, 'created_by' => $user->id]);
    $expenses = Expense::factory()->create(['moneybox_id' => $moneybox->id, 'created_by' => $user->id]);
    $incomingTransfers = MoneyboxTransaction::factory()->create(['moneybox_id' => $moneybox->id, 'transfer_to_id' => $moneybox->id, 'created_by' => $user->id]);
    $outgoingTransfers = MoneyboxTransaction::factory()->create(['moneybox_id' => $moneybox->id, 'type' => MoneyboxTransactionTypeEnum::TRANSFER->value, 'created_by' => $user->id]);

    $moneybox->update(['updated_by' => $user->id]);

    expect($moneybox->creator->id)->toBe($user->id)
        ->and($moneybox->updater->id)->toBe($user->id)
        ->and($moneybox->store->id)->toBe($store->id)
        ->and($moneybox->transactions->count())->toBe(3)
        ->and($moneybox->transactions->first()->id)->toBe($transactions->id)
        ->and($moneybox->payments->count())->toBe(1)
        ->and($moneybox->payments->first()->id)->toBe($payments->id)
        ->and($moneybox->expenses->count())->toBe(1)
        ->and($moneybox->expenses->first()->id)->toBe($expenses->id)
        ->and($moneybox->incomingTransfers->first()->id)->toBe($incomingTransfers->id)
        ->and($moneybox->outgoingTransfers->last()->id)->toBe($outgoingTransfers->id);
});
