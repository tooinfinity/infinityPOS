<?php

declare(strict_types=1);

use App\Data\CategoryData;
use App\Data\ExpenseData;
use App\Data\MoneyboxData;
use App\Data\MoneyboxTransactionData;
use App\Data\StoreData;
use App\Data\UserData;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

it('transforms an expense model into ExpenseData', function (): void {

    $creator = User::factory()->create();
    $updater = User::factory()->create();

    $category = Category::factory()->create();
    $store = Store::factory()->create();
    $moneybox = Moneybox::factory()->create();

    /** @var Expense $expense */
    $expense = Expense::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($category, 'category')
        ->for($store, 'store')
        ->for($moneybox, 'moneybox')
        ->has(MoneyboxTransaction::factory()->count(3), 'moneyboxTransactions')
        ->create([
            'amount' => 1200,
            'description' => 'Office supplies',
        ]);

    $data = ExpenseData::fromModel(
        $expense->load([
            'creator',
            'updater',
            'category',
            'store',
            'moneybox',
            'moneyboxTransactions',
        ])
    );

    expect($data)
        ->toBeInstanceOf(ExpenseData::class)
        ->id->toBe($expense->id)
        ->amount->toBe(1200)
        ->description->toBe('Office supplies')
        ->and($data->category->resolve())
        ->toBeInstanceOf(CategoryData::class)
        ->id->toBe($category->id)
        ->and($data->store->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id)
        ->and($data->moneybox->resolve())
        ->toBeInstanceOf(MoneyboxData::class)
        ->id->toBe($moneybox->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id);

    $transactions = $data->moneyboxTransactions->resolve();

    if ($transactions instanceof DataCollection) {
        expect($transactions)->toBeInstanceOf(DataCollection::class)
            ->and($transactions->count())->toBe(3);

        foreach ($transactions->all() as $tx) {
            expect($tx)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    } else {
        expect($transactions)->toBeInstanceOf(Collection::class)
            ->and($transactions->count())->toBe(3);

        foreach ($transactions as $tx) {
            expect($tx)->toBeInstanceOf(MoneyboxTransactionData::class);
        }
    }

    expect($data->created_at->toDateTimeString())
        ->toBe($expense->created_at->toDateTimeString())
        ->and($data->updated_at->toDateTimeString())
        ->toBe($expense->updated_at->toDateTimeString());
});
