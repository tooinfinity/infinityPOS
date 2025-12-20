<?php

declare(strict_types=1);

use App\Actions\Expenses\UpdateExpense;
use App\Data\Expenses\UpdateExpenseData;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\Store;
use App\Models\User;

it('may update an expense', function (): void {
    $user = User::factory()->create();
    $category1 = Category::factory()->create(['created_by' => $user->id]);
    $store1 = Store::factory()->create(['created_by' => $user->id]);
    $moneybox1 = Moneybox::factory()->create(['store_id' => $store1->id, 'created_by' => $user->id]);

    $expense = Expense::factory()->create([
        'amount' => 10000,
        'description' => 'Old expense',
        'category_id' => $category1->id,
        'store_id' => $store1->id,
        'moneybox_id' => $moneybox1->id,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $category2 = Category::factory()->create(['created_by' => $user->id]);
    $store2 = Store::factory()->create(['created_by' => $user->id]);
    $moneybox2 = Moneybox::factory()->create(['store_id' => $store2->id, 'created_by' => $user->id]);
    $action = resolve(UpdateExpense::class);

    $data = UpdateExpenseData::from([
        'amount' => 75000,
        'description' => 'Updated expense description',
        'category_id' => $category2->id,
        'store_id' => $store2->id,
        'moneybox_id' => $moneybox2->id,
        'updated_by' => $user2->id,
    ]);

    $action->handle($expense, $data);

    expect($expense->refresh()->amount)->toBe(75000)
        ->and($expense->description)->toBe('Updated expense description')
        ->and($expense->category_id)->toBe($category2->id)
        ->and($expense->store_id)->toBe($store2->id)
        ->and($expense->moneybox_id)->toBe($moneybox2->id)
        ->and($expense->updated_by)->toBe($user2->id);
});
