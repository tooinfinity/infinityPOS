<?php

declare(strict_types=1);

use App\Actions\Expenses\CreateExpense;
use App\Data\Expenses\CreateExpenseData;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\Store;
use App\Models\User;

it('may create an expense', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $moneybox = Moneybox::factory()->create(['store_id' => $store->id, 'created_by' => $user->id]);
    $action = resolve(CreateExpense::class);

    $data = CreateExpenseData::from([
        'amount' => 50000,
        'description' => 'Office supplies purchase',
        'category_id' => $category->id,
        'store_id' => $store->id,
        'moneybox_id' => $moneybox->id,
        'created_by' => $user->id,
    ]);

    $expense = $action->handle($data);

    expect($expense)->toBeInstanceOf(Expense::class)
        ->and($expense->amount)->toBe(50000)
        ->and($expense->description)->toBe('Office supplies purchase')
        ->and($expense->category_id)->toBe($category->id)
        ->and($expense->store_id)->toBe($store->id)
        ->and($expense->moneybox_id)->toBe($moneybox->id)
        ->and($expense->created_by)->toBe($user->id);
});
