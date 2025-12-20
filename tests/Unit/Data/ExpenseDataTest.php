<?php

declare(strict_types=1);

use App\Data\Categories\CategoryData;
use App\Data\Expenses\ExpenseData;
use App\Data\MoneyboxData;
use App\Data\Stores\StoreData;
use App\Data\Users\UserData;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\Store;
use App\Models\User;

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
        ->create([
            'amount' => 1200,
            'description' => 'Office supplies',
        ]);

    $data = ExpenseData::from(
        $expense->load([
            'creator',
            'updater',
            'category',
            'store',
            'moneybox',
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
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($expense->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($expense->updated_at->toDateTimeString());

});
