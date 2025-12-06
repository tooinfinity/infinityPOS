<?php

declare(strict_types=1);

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $expense = Expense::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($expense->toArray()))
        ->toBe([
            'id',
            'amount',
            'description',
            'category_id',
            'store_id',
            'moneybox_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('expense relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $category = Category::factory()->create(['type' => CategoryTypeEnum::EXPENSE->value, 'created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $moneyBox = Moneybox::factory()->create(['created_by' => $user->id]);
    $expense = Expense::factory()->create(['store_id' => $store->id, 'category_id' => $category->id, 'moneybox_id' => $moneyBox->id, 'created_by' => $user->id]);
    $moneyboxTransaction = MoneyboxTransaction::factory()->create(['expense_id' => $expense->id, 'moneybox_id' => $moneyBox->id, 'created_by' => $user->id]);

    $expense->update(['updated_by' => $user->id]);

    expect($expense->category->id)->toBe($category->id)
        ->and($expense->store->id)->toBe($store->id)
        ->and($expense->moneybox->id)->toBe($moneyBox->id)
        ->and($expense->creator->id)->toBe($user->id)
        ->and($expense->updater->id)->toBe($user->id)
        ->and($expense->moneyboxTransactions->count())->toBe(1)
        ->and($expense->moneyboxTransactions->first()->id)->toBe($moneyboxTransaction->id);

});
