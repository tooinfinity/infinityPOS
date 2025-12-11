<?php

declare(strict_types=1);

use App\Data\ExpenseData;
use App\Data\MoneyboxData;
use App\Data\MoneyboxTransactionData;
use App\Data\PaymentData;
use App\Data\StoreData;
use App\Data\UserData;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Payment;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

it('transforms an moneybox model into MoneyboxData', function (): void {

    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $store = Store::factory()->create();

    /** @var Moneybox $moneybox */
    $moneybox = Moneybox::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($store, 'store')
        ->has(MoneyboxTransaction::factory()->transfer()->count(2), 'transactions')
        ->has(Payment::factory()->count(3), 'payments')
        ->has(Expense::factory()->count(3), 'expenses')
        ->has(MoneyboxTransaction::factory()->incoming()->count(2), 'incomingTransfers')
        ->has(MoneyboxTransaction::factory()->outgoing()->count(2), 'outgoingTransfers')
        ->create([
            'name' => 'Cash',
            'type' => 'cash',
            'description' => 'Cash in hand',
            'balance' => 50000,
            'bank_name' => 'Cash',
            'account_number' => '123456789',
            'is_active' => true,
        ]);

    $moneybox->load([
        'creator',
        'updater',
        'store',
        'transactions',
        'payments',
        'expenses',
        'incomingTransfers',
        'outgoingTransfers',
    ]);

    $data = MoneyboxData::fromModel($moneybox);

    expect($data)
        ->toBeInstanceOf(MoneyboxData::class)
        ->id->toBe($moneybox->id)
        ->name->toBe('Cash')
        ->type->toBe('cash')
        ->description->toBe('Cash in hand')
        ->balance->toBe(50000)
        ->bank_name->toBe('Cash')
        ->account_number->toBe('123456789')
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->store->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id);

    $transactions = $data->transactions->resolve();

    if ($transactions instanceof DataCollection) {
        expect($transactions)->toBeInstanceOf(DataCollection::class)
            ->and($transactions->count())->toBe(4);

        foreach ($transactions->all() as $transaction) {
            expect($transaction)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    } else {
        expect($transactions)->toBeInstanceOf(Collection::class)
            ->and($transactions->count())->toBe(4);

        foreach ($transactions as $transaction) {
            expect($transaction)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    }

    $payments = $data->payments->resolve();

    if ($payments instanceof DataCollection) {
        expect($payments)->toBeInstanceOf(DataCollection::class)
            ->and($payments->count())->toBe(3);

        foreach ($payments->all() as $payment) {
            expect($payment)->toBeInstanceOf(PaymentData::class);
        }
    } else {
        expect($payments)->toBeInstanceOf(Collection::class)
            ->and($payments->count())->toBe(3);

        foreach ($payments as $payment) {
            expect($payment)->toBeInstanceOf(PaymentData::class);
        }
    }

    $expenses = $data->expenses->resolve();

    if ($expenses instanceof DataCollection) {
        expect($expenses)->toBeInstanceOf(DataCollection::class)
            ->and($expenses->count())->toBe(3);

        foreach ($expenses->all() as $expense) {
            expect($expense)->toBeInstanceOf(ExpenseData::class);
        }
    } else {
        expect($expenses)->toBeInstanceOf(Collection::class)
            ->and($expenses->count())->toBe(3);

        foreach ($expenses as $expense) {
            expect($expense)->toBeInstanceOf(ExpenseData::class);
        }
    }

    $incomingTransfers = $data->incomingTransfers->resolve();

    if ($incomingTransfers instanceof DataCollection) {
        expect($incomingTransfers)->toBeInstanceOf(DataCollection::class)
            ->and($incomingTransfers->count())->toBe(2);

        foreach ($incomingTransfers->all() as $incomingTransfer) {
            expect($incomingTransfer)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    } else {
        expect($incomingTransfers)->toBeInstanceOf(Collection::class)
            ->and($incomingTransfers->count())->toBe(2);

        foreach ($incomingTransfers as $incomingTransfer) {
            expect($incomingTransfer)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    }

    $outgoingTransfers = $data->outgoingTransfers->resolve();

    if ($outgoingTransfers instanceof DataCollection) {
        expect($outgoingTransfers)->toBeInstanceOf(DataCollection::class)
            ->and($outgoingTransfers->count())->toBe(2);

        foreach ($outgoingTransfers->all() as $outgoingTransfer) {
            expect($outgoingTransfer)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    } else {
        expect($outgoingTransfers)->toBeInstanceOf(Collection::class)
            ->and($outgoingTransfers->count())->toBe(2);

        foreach ($outgoingTransfers as $outgoingTransfer) {
            expect($outgoingTransfer)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    }

    expect($data->created_at->toDateTimeString())
        ->toBe($moneybox->created_at->toDateTimeString())
        ->and($data->updated_at->toDateTimeString())
        ->toBe($moneybox->updated_at->toDateTimeString());
});
